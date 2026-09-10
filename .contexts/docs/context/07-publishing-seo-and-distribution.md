# Module 07: Public Publishing, SEO & Outbound Distribution

## 1. Core Principle: Separation of Notice (Truth) and Post (Presentation)

```text
┌─────────────────────────────────┐
│     notices Table (Truth)       │
│  - Relational positions         │
│  - Normalized UTC timestamps    │
│  - Reservation quotas (UR/OBC)  │
│  - Verifiable evidence links    │
└────────────────┬────────────────┘
                 │
                 ▼ GeneratePublicPostAction
┌─────────────────────────────────┐
│     posts Table (Editorial)     │
│  - Clean, scannable HTML        │
│  - Schema.org JobPosting JSON-LD│
│  - SEO title & meta description │
│  - Public canonical slug        │
└────────────────┬────────────────┘
                 │
                 ├───────────────────────────────┐
                 ▼                               ▼
     Public Svelte 5 / Web UI       n8n External Broadcast Gateway
   (Googlebot / Candidate Traffic)    (Telegram, WhatsApp, Alerts)
```

1. **`notices`** stores the immutable, auditable, structured data model.
2. **`posts`** stores the reader-facing editorial presentation generated from structured facts.
3. If a notice is amended via a Corrigendum, the post content is automatically re-generated, leaving an editorial revision trail.

---

## 2. Technical SEO & JobPosting Structured Data

Every published post generates **Schema.org `JobPosting` JSON-LD** to maximize indexing on Google Jobs and search engine result pages (SERPs):

```html
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "JobPosting",
  "title": "Assistant Executive Engineer (Civil) Recruitment 2026",
  "description": "<p>Union Public Service Commission (UPSC) invites applications for 102 posts of Assistant Executive Engineer...</p>",
  "datePosted": "2026-09-01T10:00:00+05:30",
  "validThrough": "2026-10-15T23:59:59+05:30",
  "employmentType": "FULL_TIME",
  "hiringOrganization": {
    "@type": "GovernmentOrganization",
    "name": "Union Public Service Commission",
    "sameAs": "https://upsc.gov.in"
  },
  "jobLocation": {
    "@type": "Place",
    "address": {
      "@type": "PostalAddress",
      "addressCountry": "IN"
    }
  },
  "baseSalary": {
    "@type": "MonetaryAmount",
    "currency": "INR",
    "value": {
      "@type": "QuantitativeValue",
      "minValue": 56100,
      "maxValue": 177500,
      "unitText": "MONTH"
    }
  },
  "totalJobOpenings": 102
}
</script>
```

### 2.1 URL Structure & Slug Stability
- **Pattern:** `/recruitment/{institution-slug}/{position-slug}-{year}`
- **Example:** `/recruitment/upsc/assistant-executive-engineer-civil-2026`
- **Rule:** Never embed auto-incrementing integers or crawler run IDs in public URLs. Once published, slugs are permanent; changes require a 301 redirect entry in a redirect table.

---

## 3. High-Performance PostgreSQL Full-Text Search

Initial full-text search is powered natively by PostgreSQL using weighted `tsvector` and trigram similarity (`pg_trgm`) for typo tolerance:

```sql
-- Migration snippet for search vector
ALTER TABLE posts ADD COLUMN search_vector tsvector
GENERATED ALWAYS AS (
    setweight(to_tsvector('english', coalesce(title, '')), 'A') ||
    setweight(to_tsvector('english', coalesce(seo_description, '')), 'B') ||
    setweight(to_tsvector('english', coalesce(excerpt, '')), 'C')
) STORED;

CREATE INDEX idx_posts_fts ON posts USING gin (search_vector);
```

### 3.1 Typo Tolerance with Trigrams
To handle common misspellings of institutions and positions (e.g., candidate searches for *"Relway"* or *"UPSCC"*):

```sql
-- Trigram matching on title and institution
SELECT p.*, similarity(p.title, 'Relway') AS sm
FROM posts p
WHERE p.title % 'Relway' OR p.title ILIKE '%Relway%'
ORDER BY sm DESC
LIMIT 20;
```

---

## 4. Multi-Channel Outbound Distribution via n8n

When a notice reaches `status = 'published'`, Laravel emits the `NoticePublished` event. An asynchronous job sends a cryptographically signed webhook to **n8n**:

```json
// POST https://n8n.internal.network/webhook/notice-published
{
  "event": "notice.published",
  "notice_id": "01J7N4B6...",
  "institution": "Union Public Service Commission",
  "title": "UPSC Assistant Executive Engineer Recruitment 2026",
  "total_vacancies": 102,
  "application_deadline": "15 October 2026",
  "public_url": "https://platform.domain/recruitment/upsc/aee-2026",
  "official_pdf_url": "https://upsc.gov.in/.../notice.pdf",
  "categories": ["Central Government", "Engineering", "Level 10"]
}
```

### 4.1 n8n Automated Distribution Channels
1. **Telegram Channel Bot:** Formats a clean instant-view card and posts to relevant state/category Telegram channels.
2. **WhatsApp Community Gateway:** Dispatches concise notification alerts to subscribed candidates.
3. **Dynamic RSS / Atom Feeds:** Ingestion into automated feeds partitioned by state (`/feeds/state/uttar-pradesh.xml`) and qualification (`/feeds/qualification/graduate.xml`).
