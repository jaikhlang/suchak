# Module 06: Moderation Workbench & Admin UI Architecture

## 1. Moderation Paradigm: Verification Over Manual Entry

The moderation workbench is engineered to optimize **verification speed and cognitive ease**. A human operator should never have to manually re-type notice details from a PDF into form fields.

**Target Operational Metric:** A moderator can review, verify evidence grounding, and approve a standard 20-page government recruitment gazette in **under 60 seconds**.

---

## 2. Split-Screen Verification Workbench Specification

```text
┌───────────────────────────────────────┬───────────────────────────────────────┐
│ LEFT PANE (50% Width)                 │ RIGHT PANE (50% Width)                │
│ Original Document & Evidence Viewer   │ Structured Facts & Editable Fields    │
├───────────────────────────────────────┼───────────────────────────────────────┤
│ [Page 1] [Page 2] [Page 3 of 14] 🔍   │ Status: PENDING REVIEW  Confidence: 96%│
│                                       │ 🏢 Institution: UPSC                  │
│ ┌───────────────────────────────────┐ │ 📝 Title: Engineering Services Exam   │
│ │ UNION PUBLIC SERVICE COMMISSION   │ │ 🔢 Advt No: 04/2026-ENG               │
│ │                                   │ ├─────────────────────────────────────┤
│ │ Closing Date: [ 15/10/2026 ] ◄────┼─┼─► Application Deadline:              │
│ │ (Highlighted Bounding Box)        │ │   Value: 2026-10-15 [Edit]           │
│ │                                   │ │   Evidence: "Closing Date: 15/10/2026"│
│ │ Vacancies:                        │ │   Page: 2  Grounding: VERIFIED ✓     │
│ │ Civil: 45, Mech: 30, Elec: 25     │ ├─────────────────────────────────────┤
│ │ Total: 100 Posts                  │ │ 👥 Total Vacancies: 100               │
│ │                                   │ │   UR: 40 | OBC: 27 | SC: 15 | ST: 8 │
│ └───────────────────────────────────┘ │   EWS: 10                           │
├───────────────────────────────────────┼───────────────────────────────────────┤
│ [Download PDF] [View OCR Raw Text]    │ [Reject (R)] [Edit (E)] [Approve (A)] │
└───────────────────────────────────────┴───────────────────────────────────────┘
```

### 2.1 Interactive Left Pane (Document Viewer)
- Renders original PDF or HTML artifact.
- **Bi-directional Evidence Highlighting:** Clicking any field on the right pane automatically jumps the left viewer to the exact `page_number` and scrolls into view with an animated SVG bounding-box outline surrounding the `verbatim_text_fragment`.
- Fast toggle between:
  1. High-fidelity PDF viewer (PDF.js / WebViewer).
  2. OCR text layer view.
  3. Raw HTML snapshot.

### 2.2 Right Pane (Structured Inspector & Editor)
- **Top Bar:** Institution badge, trust level indicator, overall confidence score bar, and hallucination warning alert (if any).
- **Critical Fields Cards:**
  - **Reference & Title:** Advt No. with auto-duplicate search link.
  - **Timeline:** Application Start, Closing Date, Correction Window, Exam Date.
  - **Vacancies & Reservation Breakdown Matrix:** Interactive grid displaying UR, OBC, SC, ST, EWS, and horizontal quotas with instant row/column total verification.
  - **Eligibility & Qualifications:** Age limits, relaxation summary, qualification tags.
  - **Application Fee & Mode:** Mode (Online/Offline), fee amounts, fee-exempted flags.
- **Field-Level Evidence Badges:** Every field displays a micro-badge:
  - 🟢 `VERIFIED` (Exact substring matched in clean document text).
  - 🟠 `UNGROUNDED` (AI asserted value not found verbatim; requires manual confirmation).
  - 🔵 `HUMAN_EDITED` (Modified by moderator).

---

## 3. Moderator Keyboard Shortcuts (Speed Run Mode)

To facilitate high-velocity queue processing without relying heavily on mouse interaction:

| Key | Action | Behavior |
|---|---|---|
| `A` | **Approve & Publish** | Marks notice approved, generates public post, queues sitemap ping and distribution broadcast. Advances to next item. |
| `R` | **Reject Notice** | Opens quick-reason modal (e.g., duplicate, spam, tender only, invalid scan). |
| `E` | **Toggle Edit Mode** | Makes all right-pane structured fields directly editable inline. |
| `M` | **Merge Duplicate** | Opens modal to link this notice to an existing parent notice. |
| `C` | **Mark Corrigendum** | Designates notice as Corrigendum and prompts for parent notice ID. |
| `J` / `K` | **Scroll Evidence** | Cycles through extracted evidence fields, updating left pane focus. |

---

## 4. Frontend Component Blueprint (Svelte 5 + Inertia v2)

All components are written in **Svelte 5 Runes** and leverage `shadcn-svelte` primitives:

```svelte
<!-- resources/js/Components/Moderation/EvidenceField.svelte -->
<script lang="ts">
    interface Props {
        label: string;
        value: string | number | null;
        evidenceSnippet?: string;
        pageNumber?: number;
        isGrounded: boolean;
        onSelectEvidence: () => void;
    }

    let { label, value, evidenceSnippet, pageNumber, isGrounded, onSelectEvidence } = $props<Props>();
</script>

<div 
    class="p-3 rounded-lg border transition-all cursor-pointer hover:border-primary/50 bg-card text-card-foreground shadow-xs"
    onclick={onSelectEvidence}
>
    <div class="flex items-center justify-between gap-2 mb-1">
        <span class="text-xs font-medium text-muted-foreground">{label}</span>
        {#if isGrounded}
            <span class="inline-flex items-center text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 font-medium">
                Grounded (p. {pageNumber})
            </span>
        {:else}
            <span class="inline-flex items-center text-[10px] px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300 font-medium">
                Ungrounded ⚠
            </span>
        {/if}
    </div>
    <div class="text-sm font-semibold">{value ?? 'Not Specified'}</div>
    {#if evidenceSnippet}
        <div class="mt-1.5 text-xs text-muted-foreground italic truncate">
            "{evidenceSnippet}"
        </div>
    {/if}
</div>
```

---

## 5. Immutable Audit Trail

Every state change, moderator approval, rejection, and manual field override is recorded in `moderation_reviews` and `audit_logs`:

```json
// audit_logs record example
{
  "id": "01J7N5K8M2...",
  "actor_id": "01J6M123...",
  "actor_type": "user",
  "event": "notice.approved_with_corrections",
  "subject_type": "App\\Models\\Notice",
  "subject_id": "01J7N4B6...",
  "old_values": {
    "application_end_at": "2026-10-14T23:59:59Z"
  },
  "new_values": {
    "application_end_at": "2026-10-15T23:59:59Z"
  },
  "metadata": {
    "reason": "Corrected closing date based on page 2 paragraph 4 of gazette",
    "ip_address": "203.0.113.195",
    "user_agent": "Mozilla/5.0..."
  }
}
```
