# Module 04: Indian Recruitment Domain Rules & Logic

## 1. Domain Nuances & Context

Indian recruitment and examination notices are governed by strict constitutional, statutory, and administrative frameworks. The platform must model these realities with high fidelity rather than treating recruitment as generic blog posts.

---

## 2. Corrigendum & Addendum Lifecycle Protocol ("शुद्धिपत्र")

In Indian public sector recruitment, it is routine for government boards (UPSC, SSC, State PSCs, Railway RRB) to release **Corrigenda (शुद्धिपत्र)**, **Addenda**, or **Cancellation Notices** weeks or months after the initial notification.

```text
               ┌──────────────────────────────┐
               │ Primary Notification (Notice)│
               │ Advt No: 05/2026             │
               │ Deadline: 2026-10-15         │
               │ Vacancies: 150               │
               └──────────────┬───────────────┘
                              │
               New Artifact Ingested: Corrigendum-1
                              │
                              ▼
               ┌──────────────────────────────┐
               │ Corrigendum Ingestion Engine │
               │  - Matches Parent Notice ID  │
               │  - Extracts Field Patches    │
               │  - Verifies Grounded Evidence│
               └──────────────┬───────────────┘
                              │
                              ▼
               ┌──────────────────────────────┐
               │ Notice Revision Created (v2) │
               │ Diff:                        │
               │  application_end_at: 2026-10-30
               │  total_vacancies: 180        │
               └──────────────┬───────────────┘
                              │
                              ▼
               ┌──────────────────────────────┐
               │ Parent Notice Patched        │
               │ Linked via parent_notice_id  │
               │ Public Alert Generated       │
               └──────────────────────────────┘
```

### 2.1 Corrigendum Classification Rules
A document is classified as a Corrigendum when:
1. The title or document header contains terms: *"Corrigendum"*, *"Addendum"*, *"Notice for Extension of Last Date"*, *"शुद्धिपत्र"*, *"संशोधन"*, *"Amendment"*.
2. The document cites an existing official notification or reference number (`parent_notice_id`).

### 2.2 Patching Execution Rules
When a Corrigendum is verified:
1. **Never create a disconnected orphan notice.**
2. Create a new `notice_revisions` record capturing:
   - `snapshot_data`: Full snapshot of the parent notice state before the patch.
   - `diff_data`: Explicit JSON dictionary of fields being modified:
     ```json
     {
       "application_end_at": {
         "old": "2026-10-15T23:59:59Z",
         "new": "2026-10-31T23:59:59Z"
       },
       "total_vacancies": {
         "old": 150,
         "new": 185
       }
     }
     ```
3. Update the parent `notices` record in a single database transaction.
4. If the parent notice is already published, emit `NoticeChanged` event to trigger public alert banners and subscriber notifications.

---

## 3. Reservation & Quotas (Vertical & Horizontal)

In India, vacancies are categorized by strict vertical and horizontal reservation policies under constitutional mandates.

### 3.1 Vertical Reservations (Categorical)
Every position's vacancy table specifies allocations across:
- **UR (Unreserved / General):** Open to all merit candidates.
- **OBC-NCL (Other Backward Classes - Non-Creamy Layer):** 27% standard central quota.
- **SC (Scheduled Castes):** 15% standard central quota.
- **ST (Scheduled Tribes):** 7.5% standard central quota.
- **EWS (Economically Weaker Sections):** 10% standard central quota.

### 3.2 Horizontal Reservations (Inter-locking)
Horizontal quotas cut across vertical categories:
- **PwBD (Persons with Benchmark Disabilities):**
  - Category A: Blindness and low vision (VH)
  - Category B: Deaf and hard of hearing (HH)
  - Category C: Locomotor disability including cerebral palsy, leprosy cured, dwarfism, acid attack victims (OH)
  - Category D & E: Autism, intellectual disability, specific learning disability, mental illness, multiple disabilities
- **Ex-Servicemen (ESM):** Typically in Group C & D posts, police, and security forces.
- **Women Reservation:** Up to 33% to 35% horizontal reservation in several states (e.g., Bihar, Uttar Pradesh, Madhya Pradesh).
- **Meritorious Sportspersons.**

> [!IMPORTANT]
> The database stores reservations in the `position_reservations` table (Module 02) so candidates can filter vacancies by their specific category and sub-category.

---

## 4. Age Calculation & Statutory Relaxations

Government recruitments define an exact **cutoff date for age calculation** (e.g., *"Age as on 01.08.2026"*).

### 4.1 Standard Age Relaxations Matrix
When extracting and evaluating eligibility, apply standard statutory relaxation offsets:

| Category | Typical Upper Age Relaxation |
|---|---|
| **SC / ST** | +5 years |
| **OBC (Non-Creamy Layer)** | +3 years |
| **PwBD (General / EWS)** | +10 years |
| **PwBD (OBC-NCL)** | +13 years |
| **PwBD (SC / ST)** | +15 years |
| **Ex-Servicemen (ESM)** | Service years in armed forces + 3 years |
| **Departmental Candidates** | As per specific service rules (up to 5 to 10 years) |

---

## 5. Application Fee & Exemption Rules

Recruitment portals have category-specific fee structures. Candidates frequently search for *"Free / No Fee"* recruitment updates.

### 5.1 Structure & Exemptions
The `application_details` model must capture:
- **Standard Unreserved Fee:** (e.g., ₹100, ₹500, ₹1,000).
- **Exempted Categories:** SC, ST, PwBD, and Female candidates are frequently 100% exempted from paying application fees (e.g., UPSC and SSC standard notifications).
- **Payment Modes:**
  - **Online:** SBI ePay, Debit Card, Credit Card, Netbanking, UPI.
  - **Offline:** SBI Challan, Demand Draft (DD) in favor of the designated authority, Indian Postal Order (IPO).

---

## 6. Authoritative TLD Whitelist & Trust Scoring

To eliminate fake recruitment scams and clickbait job portals, the crawler enforces an **Authoritative Domain Trust Engine**:

| Trust Level | TLD / Domain Patterns | Action Policy |
|---|---|---|
| **`OFFICIAL_VERIFIED`** | `*.gov.in`<br>`*.nic.in`<br>`*.nic.in/*`<br>Verified official state subdomains (e.g., `uppsc.up.nic.in`, `bpsc.bih.nic.in`, `upsc.gov.in`) | Automatically eligible for auto-publication if extraction confidence $> 0.95$ and grounding passes. |
| **`INSTITUTIONAL_VERIFIED`**| `*.ac.in`<br>`*.edu.in`<br>`*.res.in`<br>(IITs, IIMs, NITs, Central Universities, CSIR, ISRO, DRDO) | Eligible for auto-publication under institutional policy. |
| **`PUBLIC_SECTOR_VERIFIED`** | Verified domains of Maharatna, Navratna, and Miniratna PSUs (e.g., `ongcindia.com`, `sail.co.in`, `bhel.com`, `ntpc.co.in`) | Pre-approved domain whitelist required. |
| **`DISCOVERY_ONLY`** | Third-party aggregators (`.com`, `.in`, `.org`, `.net` like SarkariResult, FreeJobAlert, Testbook) | **NEVER publish directly from these sources.** Used strictly as signals to trigger crawl jobs on the primary `.gov.in` target. |

---

## 7. Legal & Copyright Standing

Under **Section 52(1)(q) of the Indian Copyright Act, 1957**, the reproduction or publication of:
- Any matter which has been published in any Official Gazette;
- Any Act of a Legislature;
- Any report of any committee, commission, council, or other like body appointed by the Government;
- Any judgment, order, or decree of a court;
**does not constitute an infringement of copyright.**

Official government recruitment notices, examination notifications, and syllabi published by Central/State commissions and statutory bodies fall under this exemption. However:
1. The platform must always attribute the recruiting authority and link to the original official portal URL.
2. The platform must never claim to be an official government entity or misrepresent itself as the recruiting authority.
3. Every public post must feature a disclaimer:
   > *"Information published here is sourced from the official notification released by [Institution Name]. Candidates are advised to verify details on the official portal ([official_url]) before applying."*
