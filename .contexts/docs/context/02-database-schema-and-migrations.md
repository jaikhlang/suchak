# Module 02: Database Schema & Migration Strategy

## 1. Relational Design Standards

1. **Identifier Strategy:** Use **ULID (Universally Unique Lexicographically Sortable Identifiers)** for all primary keys (`ulid` column type, 26 characters). ULIDs ensure chronological sortability, clean index distribution, and zero enumeration leakage.
2. **Timestamps:** Store all dates and timestamps as **UTC `timestamptz`** in PostgreSQL. Normalization to Indian Standard Time (`Asia/Kolkata` / UTC+05:30) occurs at the application/presentation layer.
3. **Immutability & Provenance:** Foreign keys link every piece of extracted data back to the `source_artifacts` and `evidence` records that produced it.
4. **JSONB Usage Rules:** JSONB is restricted to polymorphic metadata, raw API dumps, OCR bounding coordinates, and dynamic crawler settings. Core queryable recruitment metrics (dates, vacancies, reservations, eligibility) **must be first-class relational columns**.

---

## 2. Core Relational Schema

### 2.1 Geographic Hierarchy (India)

```sql
-- 1. states
CREATE TABLE states (
    id ULID PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    iso_code VARCHAR(10) NOT NULL UNIQUE, -- e.g., 'IN-UP', 'IN-MH', 'IN-DL'
    type VARCHAR(30) NOT NULL DEFAULT 'state', -- 'state' or 'union_territory'
    capital VARCHAR(100),
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL
);

-- 2. districts
CREATE TABLE districts (
    id ULID PRIMARY KEY,
    state_id ULID NOT NULL REFERENCES states(id) ON DELETE RESTRICT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL,
    UNIQUE(state_id, slug)
);
```

---

### 2.2 Institutions & Alias Resolution

```sql
-- 3. institutions
CREATE TABLE institutions (
    id ULID PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    short_name VARCHAR(50),                     -- e.g., 'UPSC', 'SSC', 'IBPS', 'ISRO'
    slug VARCHAR(255) NOT NULL UNIQUE,
    institution_type VARCHAR(50) NOT NULL,      -- 'central_gov', 'state_gov', 'psu', 'autonomous', 'banking', 'defence', 'court'
    parent_id ULID REFERENCES institutions(id) ON DELETE SET NULL,
    state_id ULID REFERENCES states(id) ON DELETE SET NULL, -- NULL for Central authorities
    website_url TEXT NOT NULL,
    official_domain VARCHAR(255) NOT NULL,      -- e.g., 'upsc.gov.in' (must match TLD whitelist)
    is_verified BOOLEAN NOT NULL DEFAULT false,
    is_active BOOLEAN NOT NULL DEFAULT true,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL
);

-- 4. institution_aliases (Multi-lingual & Acronym Entity Resolution)
CREATE TABLE institution_aliases (
    id ULID PRIMARY KEY,
    institution_id ULID NOT NULL REFERENCES institutions(id) ON DELETE CASCADE,
    alias VARCHAR(255) NOT NULL,                 -- e.g., 'संघ लोक सेवा आयोग', 'Union Public Service Commission'
    locale VARCHAR(10) NOT NULL DEFAULT 'en',   -- 'en', 'hi', etc.
    is_primary BOOLEAN NOT NULL DEFAULT false,
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL,
    UNIQUE(alias, locale)
);
```

---

### 2.3 Sources, Crawl Tracking & Artifacts

```sql
-- 5. sources
CREATE TABLE sources (
    id ULID PRIMARY KEY,
    institution_id ULID NOT NULL REFERENCES institutions(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,                 -- e.g., 'UPSC Active Examinations Noticeboard'
    type VARCHAR(50) NOT NULL,                  -- 'recruitment_portal', 'pdf_noticeboard', 'rss', 'sitemap'
    url TEXT NOT NULL,
    canonical_url TEXT,
    domain VARCHAR(255) NOT NULL,
    crawl_method VARCHAR(50) NOT NULL,          -- 'http_static', 'playwright_browser', 'api'
    crawl_frequency_minutes INTEGER NOT NULL DEFAULT 120,
    status VARCHAR(30) NOT NULL DEFAULT 'active', -- 'active', 'paused', 'failing', 'disabled'
    trust_level VARCHAR(30) NOT NULL,           -- 'official_verified', 'institutional', 'discovery_only'
    consecutive_failures INTEGER NOT NULL DEFAULT 0,
    last_crawled_at TIMESTAMPTZ,
    next_crawl_at TIMESTAMPTZ,
    last_success_at TIMESTAMPTZ,
    last_failure_at TIMESTAMPTZ,
    configuration JSONB DEFAULT '{}',           -- CSS selectors, pagination, delays, headers
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL
);

-- 6. crawl_runs
CREATE TABLE crawl_runs (
    id ULID PRIMARY KEY,
    source_id ULID NOT NULL REFERENCES sources(id) ON DELETE CASCADE,
    status VARCHAR(30) NOT NULL,                -- 'queued', 'running', 'completed', 'failed'
    started_at TIMESTAMPTZ NOT NULL,
    finished_at TIMESTAMPTZ,
    items_discovered INTEGER NOT NULL DEFAULT 0,
    items_fetched INTEGER NOT NULL DEFAULT 0,
    items_failed INTEGER NOT NULL DEFAULT 0,
    http_status INTEGER,
    error_code VARCHAR(100),
    error_message TEXT,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL
);

-- 7. source_artifacts
CREATE TABLE source_artifacts (
    id ULID PRIMARY KEY,
    source_id ULID NOT NULL REFERENCES sources(id) ON DELETE CASCADE,
    crawl_run_id ULID NOT NULL REFERENCES crawl_runs(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL,                  -- 'pdf', 'html', 'image', 'json'
    url TEXT NOT NULL,
    canonical_url TEXT NOT NULL,
    content_hash VARCHAR(64) NOT NULL,          -- SHA-256 of downloaded binary
    etag VARCHAR(255),
    last_modified_header VARCHAR(255),
    mime_type VARCHAR(100) NOT NULL,
    http_status INTEGER NOT NULL,
    storage_disk VARCHAR(50) NOT NULL DEFAULT 's3',
    storage_path TEXT NOT NULL,                 -- e.g., 'artifacts/sources/{id}/{year}/{hash}.pdf'
    file_size_bytes BIGINT NOT NULL,
    title VARCHAR(500),
    retrieved_at TIMESTAMPTZ NOT NULL,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL,
    UNIQUE(source_id, content_hash)             -- Prevents re-storing identical content
);

-- 8. artifact_extractions
CREATE TABLE artifact_extractions (
    id ULID PRIMARY KEY,
    artifact_id ULID NOT NULL REFERENCES source_artifacts(id) ON DELETE CASCADE,
    method VARCHAR(50) NOT NULL,                -- 'docling_table', 'pymupdf_text', 'paddle_ocr', 'html_selector'
    status VARCHAR(30) NOT NULL,                -- 'processing', 'completed', 'failed'
    raw_text TEXT,
    clean_text TEXT,
    page_count INTEGER,
    confidence_score NUMERIC(5,4),              -- 0.0000 to 1.0000
    processor_name VARCHAR(100) NOT NULL,       -- e.g., 'docling-v2', 'paddle-ocr-v3'
    started_at TIMESTAMPTZ NOT NULL,
    finished_at TIMESTAMPTZ,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL
);
```

---

### 2.4 Structured Recruitment Notices

```sql
-- 9. notices (System of Record for Notice Truth)
CREATE TABLE notices (
    id ULID PRIMARY KEY,
    institution_id ULID NOT NULL REFERENCES institutions(id) ON DELETE RESTRICT,
    source_id ULID NOT NULL REFERENCES sources(id) ON DELETE RESTRICT,
    source_artifact_id ULID NOT NULL REFERENCES source_artifacts(id) ON DELETE RESTRICT,
    notice_type VARCHAR(50) NOT NULL,           -- 'recruitment', 'corrigendum', 'extension', 'exam_date', 'result', 'admit_card'
    title VARCHAR(500) NOT NULL,
    slug VARCHAR(600) NOT NULL UNIQUE,
    reference_number VARCHAR(255),              -- Advt No. / Notification No. e.g., '07/2026-ENG'
    summary TEXT,
    status VARCHAR(30) NOT NULL DEFAULT 'discovered', -- 'discovered', 'extracted', 'pending_review', 'approved', 'published', 'rejected', 'superseded', 'cancelled'
    confidence_score NUMERIC(5,4) NOT NULL,
    is_corrigendum BOOLEAN NOT NULL DEFAULT false,
    parent_notice_id ULID REFERENCES notices(id) ON DELETE SET NULL, -- Populated if this is a Corrigendum
    published_at TIMESTAMPTZ,                   -- Date stated in official notice
    application_start_at TIMESTAMPTZ,
    application_end_at TIMESTAMPTZ,
    fee_payment_end_at TIMESTAMPTZ,
    correction_window_end_at TIMESTAMPTZ,
    tentative_exam_date_text VARCHAR(255),
    exam_start_at TIMESTAMPTZ,
    exam_end_at TIMESTAMPTZ,
    canonical_source_url TEXT NOT NULL,
    total_vacancies INTEGER DEFAULT 0,
    is_featured BOOLEAN NOT NULL DEFAULT false,
    first_seen_at TIMESTAMPTZ NOT NULL,
    last_seen_at TIMESTAMPTZ NOT NULL,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL
);

-- 10. positions (Cadre / Post details)
CREATE TABLE positions (
    id ULID PRIMARY KEY,
    notice_id ULID NOT NULL REFERENCES notices(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,                -- e.g., 'Assistant Executive Engineer (Civil)'
    post_code VARCHAR(100),
    department VARCHAR(255),
    total_vacancies INTEGER NOT NULL DEFAULT 0,
    employment_type VARCHAR(50) NOT NULL DEFAULT 'permanent', -- 'permanent', 'contractual', 'deputation', 'apprentice'
    pay_level VARCHAR(50),                      -- e.g., 'Level 10 (7th CPC)'
    pay_scale_text VARCHAR(255),                -- e.g., 'Rs. 56,100 - 1,77,500/-'
    salary_min INTEGER,
    salary_max INTEGER,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL
);

-- 11. position_reservations (Indian Quota Breakdown)
CREATE TABLE position_reservations (
    id ULID PRIMARY KEY,
    position_id ULID NOT NULL REFERENCES positions(id) ON DELETE CASCADE,
    category VARCHAR(50) NOT NULL,              -- 'UR', 'OBC_NCL', 'SC', 'ST', 'EWS', 'PWBD_OH', 'PWBD_HH', 'PWBD_VH', 'EX_SERVICEMEN', 'WOMEN'
    quota_type VARCHAR(20) NOT NULL DEFAULT 'vertical', -- 'vertical' or 'horizontal'
    vacancies INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL,
    UNIQUE(position_id, category)
);
```

---

### 2.5 Eligibility, Qualifications & Fees

```sql
-- 12. eligibility_rules
CREATE TABLE eligibility_rules (
    id ULID PRIMARY KEY,
    notice_id ULID NOT NULL REFERENCES notices(id) ON DELETE CASCADE,
    minimum_age INTEGER,
    maximum_age INTEGER,
    age_calculated_as_on DATE,
    age_relaxation_json JSONB DEFAULT '{}',      -- e.g., {"SC_ST": 5, "OBC": 3, "PWBD": 10}
    qualification_summary TEXT,
    experience_text TEXT,
    nationality_text VARCHAR(255) DEFAULT 'Citizen of India',
    raw_eligibility_text TEXT,
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL
);

-- 13. qualifications
CREATE TABLE qualifications (
    id ULID PRIMARY KEY,
    name VARCHAR(255) NOT NULL,                 -- e.g., 'B.E. / B.Tech', 'Diploma', 'Class 10 (Matriculation)'
    slug VARCHAR(255) NOT NULL UNIQUE,
    level VARCHAR(50) NOT NULL,                 -- '10th', '12th', 'diploma', 'graduate', 'post_graduate', 'doctorate'
    discipline VARCHAR(100),                    -- 'Civil Engineering', 'Computer Science', 'Commerce'
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL
);

-- 14. notice_qualifications (Pivot)
CREATE TABLE notice_qualifications (
    notice_id ULID NOT NULL REFERENCES notices(id) ON DELETE CASCADE,
    qualification_id ULID NOT NULL REFERENCES qualifications(id) ON DELETE RESTRICT,
    is_mandatory BOOLEAN NOT NULL DEFAULT true,
    min_percentage NUMERIC(4,2),
    PRIMARY KEY(notice_id, qualification_id)
);

-- 15. application_details
CREATE TABLE application_details (
    id ULID PRIMARY KEY,
    notice_id ULID NOT NULL REFERENCES notices(id) ON DELETE CASCADE UNIQUE,
    application_mode VARCHAR(30) NOT NULL,      -- 'online', 'offline_postal', 'walk_in', 'email'
    apply_url TEXT,
    official_notification_pdf_url TEXT,
    general_fee INTEGER DEFAULT 0,              -- Fee in INR
    reserved_fee INTEGER DEFAULT 0,
    female_fee INTEGER DEFAULT 0,
    is_exempted_for_sc_st BOOLEAN DEFAULT false,
    is_exempted_for_female BOOLEAN DEFAULT false,
    is_exempted_for_pwbd BOOLEAN DEFAULT false,
    offline_postal_address TEXT,
    postal_pincode VARCHAR(10),
    instructions TEXT,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL
);
```

---

### 2.6 Evidence, Revisions, Posts & Moderation

```sql
-- 16. evidence (Provenance & Verbatim Grounding)
CREATE TABLE evidence (
    id ULID PRIMARY KEY,
    artifact_id ULID NOT NULL REFERENCES source_artifacts(id) ON DELETE CASCADE,
    extraction_id ULID NOT NULL REFERENCES artifact_extractions(id) ON DELETE CASCADE,
    notice_id ULID NOT NULL REFERENCES notices(id) ON DELETE CASCADE,
    field_name VARCHAR(100) NOT NULL,           -- e.g., 'application_end_at', 'total_vacancies'
    extracted_value TEXT NOT NULL,
    page_number INTEGER,
    verbatim_text_fragment TEXT NOT NULL,       -- Exact snippet from document
    char_start_offset INTEGER,
    char_end_offset INTEGER,
    bounding_box JSONB,                         -- {"x1": 100, "y1": 200, "x2": 450, "y2": 240}
    confidence_score NUMERIC(5,4) NOT NULL,
    is_verified_by_human BOOLEAN NOT NULL DEFAULT false,
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL
);

-- 17. notice_revisions (Immutable Change Audit & Corrigendum Patches)
CREATE TABLE notice_revisions (
    id ULID PRIMARY KEY,
    notice_id ULID NOT NULL REFERENCES notices(id) ON DELETE CASCADE,
    version_number INTEGER NOT NULL,
    change_type VARCHAR(50) NOT NULL,           -- 'initial', 'corrigendum_patch', 'extension', 'vacancy_change', 'manual_edit'
    change_reason TEXT,
    triggering_artifact_id ULID REFERENCES source_artifacts(id) ON DELETE SET NULL,
    snapshot_data JSONB NOT NULL,               -- Full JSON snapshot of Notice + Positions before change
    diff_data JSONB NOT NULL,                   -- {"field": {"old": "...", "new": "..."}}
    created_by_user_id ULID,                    -- NULL if triggered by automated Corrigendum pipeline
    created_at TIMESTAMPTZ NOT NULL
);

-- 18. posts (Public Editorial Presentation)
CREATE TABLE posts (
    id ULID PRIMARY KEY,
    notice_id ULID NOT NULL REFERENCES notices(id) ON DELETE RESTRICT UNIQUE,
    title VARCHAR(600) NOT NULL,
    slug VARCHAR(650) NOT NULL UNIQUE,
    excerpt TEXT NOT NULL,
    content_html TEXT NOT NULL,                 -- Structured, clean editorial markup
    status VARCHAR(30) NOT NULL DEFAULT 'draft',-- 'draft', 'scheduled', 'published', 'archived'
    seo_title VARCHAR(150),
    seo_description VARCHAR(255),
    canonical_url TEXT NOT NULL,
    published_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL
);

-- 19. moderation_reviews
CREATE TABLE moderation_reviews (
    id ULID PRIMARY KEY,
    notice_id ULID NOT NULL REFERENCES notices(id) ON DELETE CASCADE,
    reviewer_id ULID NOT NULL,                  -- User ULID
    action VARCHAR(50) NOT NULL,                -- 'approve', 'reject', 'request_reextraction', 'apply_manual_patch'
    notes TEXT,
    field_corrections JSONB DEFAULT '{}',       -- Exact fields modified by moderator
    reviewed_at TIMESTAMPTZ NOT NULL,
    created_at TIMESTAMPTZ NOT NULL
);
```

---

## 3. High-Performance Indexing Strategy

```sql
-- Source and Ingestion Lookups
CREATE INDEX idx_sources_crawl_schedule ON sources (status, next_crawl_at) WHERE status = 'active';
CREATE INDEX idx_artifacts_lookup ON source_artifacts (source_id, content_hash);

-- Notice Filtering and Deadlines
CREATE INDEX idx_notices_status_deadline ON notices (status, application_end_at);
CREATE INDEX idx_notices_institution ON notices (institution_id, status);
CREATE INDEX idx_notices_ref_no ON notices (reference_number) WHERE reference_number IS NOT NULL;
CREATE INDEX idx_notices_parent ON notices (parent_notice_id) WHERE parent_notice_id IS NOT NULL;

-- Position & Reservation Searches
CREATE INDEX idx_reservations_category ON position_reservations (category, vacancies) WHERE vacancies > 0;

-- Evidence Lookups for Verification UI
CREATE INDEX idx_evidence_notice_field ON evidence (notice_id, field_name);

-- PostgreSQL Full-Text Search on Notices & Posts
CREATE INDEX idx_notices_title_trgm ON notices USING gin (title gin_trgm_ops);
CREATE INDEX idx_posts_search_vector ON posts USING gin (to_tsvector('english', title || ' ' || excerpt));
```
