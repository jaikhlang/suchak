# Module 03: Ingestion, OCR & Crawling Pipeline

## 1. Pipeline Architecture Overview

The ingestion subsystem processes external resources across five distinct layers:

```text
1. Discovery & Scheduling (Laravel Scheduler / Sources Registry)
         ↓
2. Fetch & Storage (Laravel HTTP / Python Playwright Worker)
         ↓
3. Document Ingestion (Docling Table Parser / PyMuPDF / PaddleOCR)
         ↓
4. Deduplication & Change Detection (Multi-tier Matching Engine)
         ↓
5. Entity Grounding & Evidence Linking (Character offsets & Bounding Boxes)
```

---

## 2. Ingestion Sidecar Worker Contract (FastAPI)

The **Python Ingestion Sidecar** operates as an internal microservice exposing high-performance document parsing and OCR endpoints to Laravel workers.

### 2.1 Sidecar API Endpoints

| Method | Route | Description | Engine |
|---|---|---|---|
| `POST` | `/api/v1/crawl/render` | Headless browser navigation for dynamic JS portals (NIC / TCS iON) | Playwright (Chromium) |
| `POST` | `/api/v1/extract/tables` | Structured table & layout tree extraction from complex PDFs | Docling / PyMuPDF |
| `POST` | `/api/v1/ocr/process` | High-accuracy bilingual Devanagari (Hindi) & English OCR | PaddleOCR v3 / Docling |
| `POST` | `/api/v1/fingerprint` | Generate structural layout hash for PDF deduplication | LayoutLM / MinHash |

### 2.2 Table Extraction Payload Contract

When Laravel downloads a PDF notice artifact, it dispatches a job to the Python worker:

```json
// POST /api/v1/extract/tables
{
  "artifact_id": "01J7N4B6Q5G8C9R1V2T3X4Y5Z6",
  "storage_disk": "s3",
  "storage_path": "artifacts/sources/01J7N.../notice.pdf",
  "extract_tables": true,
  "language_hints": ["en", "hi"],
  "options": {
    "ocr_fallback": true,
    "detect_merged_headers": true
  }
}
```

### 2.3 Response Payload Contract

```json
{
  "artifact_id": "01J7N4B6Q5G8C9R1V2T3X4Y5Z6",
  "page_count": 12,
  "confidence": 0.965,
  "clean_text": "UNION PUBLIC SERVICE COMMISSION...\nADVERTISEMENT NO. 08/2026...",
  "tables": [
    {
      "page_number": 3,
      "table_index": 0,
      "bounding_box": {"x1": 45.2, "y1": 120.0, "x2": 550.8, "y2": 420.5},
      "headers": ["Post Title", "Pay Level", "UR", "OBC", "SC", "ST", "EWS", "Total"],
      "rows": [
        ["Assistant Engineer (Civil)", "Level 10", "42", "27", "15", "08", "10", "102"],
        ["Junior Hydrogeologist", "Level 8", "12", "08", "04", "02", "03", "29"]
      ]
    }
  ],
  "extracted_dates": [
    {"raw": "15-10-2026", "context": "Closing date for online application", "page": 1}
  ]
}
```

---

## 3. Bilingual OCR Engine (Hindi / Devanagari + English)

A significant percentage of Indian state gazettes, police recruitment notices, and regional commission circulars (e.g., UPPSC, BPSC, MPPSC) are published as scanned, low-resolution PDFs with Hindi and English text appearing side-by-side or alternating by paragraph.

### 3.1 OCR Pipeline Strategy
1. **Pre-processing:** Deskewing, contrast normalization, and adaptive thresholding using OpenCV.
2. **Text Detection & Recognition:** PaddleOCR v3 configured with bilingual dictionaries (`ch_PP-OCRv3_rec` / `en_PP-OCRv3_rec` / Hindi fine-tuned weights).
3. **Bounding Box Normalization:** Normalizing all spatial coordinates relative to page dimensions $(x_1, y_1, x_2, y_2 \in [0, 1000])$.
4. **Confidence Thresholding:** Any OCR line with character confidence $< 0.80$ is flagged for human review.

---

## 4. Multi-Level Deduplication Engine

To prevent spamming the moderation queue and public portal with duplicates (e.g., newspapers uploading re-scans, duplicate PDF uploads with different filenames, or multiple portals mirroring the same notice), the system executes a 5-tier deduplication check:

```text
Level 1: Canonical URL Normalization
   │ Match found? ──► Link to existing Artifact & Stop
   ▼
Level 2: Binary SHA-256 Content Hash
   │ Match found? ──► Mark identical Artifact & Stop
   ▼
Level 3: Normalized Official Reference Number (Advt No.)
   │ Match found? ──► Determine if Corrigendum or Duplicate
   ▼
Level 4: Semantic Vector Similarity (Cosine > 0.94)
   │ Match found? ──► Route to Duplicate Resolution Workflow
   ▼
Level 5: Document Layout Fingerprinting (Table structure & page layout)
   │ Match found? ──► Flag as Candidate Duplicate
   ▼
Passes All Tiers: Classified as Brand New Candidate Notice
```

### 4.1 Canonical URL Rules
Before hashing or fetching:
- Strip tracking parameters (`utm_*`, `fbclid`, `sessionid`, `ref`).
- Normalize protocol to `https://`.
- Lowercase domain name and remove redundant default ports (`:80`, `:443`).
- Remove trailing slashes on non-root paths.

### 4.2 Reference Number Extraction & Normalization
Indian official reference numbers have erratic formatting across different circulars:
- Raw examples: `Advt. No. 04/2026/Rectt.`, `ADVERTISEMENT NO: 04 / 2026 - RECTT`, `Notice No. 4/2026`.
- Normalized key: `04-2026-RECTT`.
- If an incoming document shares the same normalized reference number with an existing notice:
  - If title contains *"Corrigendum"*, *"Extension"*, or *"Notice regarding change in..."* $\rightarrow$ **Trigger Corrigendum Patching Protocol** (Module 04).
  - If content is identical $\rightarrow$ **Mark Duplicate**.

---

## 5. Crawler Security & Robustness

### 5.1 Strict SSRF Protection (Server-Side Request Forgery)
Because crawler URLs may be dynamically discovered from external feeds or admin inputs, strict network restrictions must be enforced before any HTTP connection is initiated:

```php
// In app/Services/Crawling/SafeHttpClient.php
public function validateTargetUrl(string $url): void
{
    $host = parse_url($url, PHP_URL_HOST);
    $ips = dns_get_record($host, DNS_A + DNS_AAAA);

    foreach ($ips as $record) {
        $ip = $record['ip'] ?? $record['ipv6'];
        if ($this->isBlockedIpRange($ip)) {
            throw new SecurityException("SSRF Attempt Blocked: Host {$host} resolves to prohibited IP {$ip}");
        }
    }
}
```

**Prohibited IP Ranges:**
- `127.0.0.0/8` (Loopback)
- `10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16` (Private RFC 1918)
- `169.254.0.0/16` (Link-Local / AWS & GCP Instance Metadata `169.254.169.254`)
- `::1` and `fc00::/7` (IPv6 loopback & unique local)

### 5.2 Polite Crawling & Rate Limiting
1. **Concurrency Caps:** Default maximum of **1 concurrent connection per government host**.
2. **Backoff Delays:** 2,000ms base delay between sequential requests to the same domain.
3. **HTTP Caching Respect:** Store and send `If-None-Match` (ETag) and `If-Modified-Since` headers to avoid re-downloading unchanged 50MB gazette files.
4. **Exponential Failure Backoff:** If a host returns HTTP `429 Too Many Requests` or `503 Service Unavailable`, back off exponentially ($2^n \times 60$ seconds, max 24 hours).
