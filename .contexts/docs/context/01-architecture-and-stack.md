# Module 01: System Architecture & Technology Stack — Project Suchak (सूचक)

## 1. High-Level Vision & Paradigm

**Suchak** (सूचक — meaning the informant or notifier of public vacancies) is an automation-first, intelligence-driven system that continuously monitors, ingests, processes, verifies, and publishes employment, recruitment, exam, and vacancy updates from authoritative Indian government, statutory, public-sector, educational, and examination authorities.

The platform is designed around **automation with human supervision**:
1. **Automation-First**: 90%+ of repetitive crawling, artifact downloading, text extraction, OCR, tabular recruitment parsing, categorization, and deduplication is handled autonomously.
2. **Provenance & Verification**: Every extracted fact (vacancy counts, reservation quotas, application deadlines, pay levels, eligibility) is immutably anchored to raw source artifacts and highlighted text evidence.
3. **Supervisor Role**: Administrators and moderators operate as reviewers and exception-handlers rather than manual data-entry clerks.

---

## 2. Pinned Technology Matrix (2026 Production Baseline)

| Layer | Technology | Pinned Version | Justification / Role |
|---|---|---|---|
| **Core Framework** | **Laravel** | **13.x** | Authoritative System of Record, business logic, DTO orchestration, Eloquent ORM, event-driven domain pipeline. |
| **Language Runtime** | **PHP** | **8.4+ / 8.5** | Asymmetric visibility, property hooks, typed properties, JIT optimizations. |
| **Local Development**| **Laravel Herd** | Latest (Win/Mac) | Native, high-velocity local runtime hosting `http://suchak.test` with zero-container PHP overhead. |
| **Frontend Framework** | **Inertia.js** | **v2.x** (`@inertiajs/svelte`) | Seamless monolith bridge between Laravel controllers and Svelte components without REST serialization boilerplate. |
| **Client UI Engine** | **Svelte** | **5.x** (Runes Mode) | Reactive UI using `$state`, `$derived`, `$props` for high-performance split-screen moderation and fast public rendering. |
| **Styling** | **Tailwind CSS** | **v4.x** | CSS-first configuration (`@theme`), high performance, minimal build overhead. |
| **Component Kit** | **shadcn-svelte** | Latest (`bits-ui`) | Accessible, unstyled primitives customized for dense data tables, modal dialogs, and verification split-views. |
| **Icons** | **Lucide Svelte** | Latest | Consistent icon set for UI statuses, badges, document types. |
| **Primary Database** | **PostgreSQL** | **17+** | Authoritative data store using ULID keys, native JSONB, GIN indexing, generated columns, and `pg_trgm` fuzzy matching. |
| **Cache & Transient State** | **Redis / Valkey** | **Redis 7.4+ / Valkey 8+** | High-throughput queues, distributed mutex locks, rate limiting, and ephemeral crawl caches. |
| **Ingestion Sidecar** | **Python** | **3.12+ / 3.13** | Dedicated worker microservice (FastAPI + Celery/ARQ) for heavy document tasks: Docling/PyMuPDF tables, PaddleOCR, Playwright. |
| **OCR Engines** | **PaddleOCR / Docling** | Latest | Bilingual (Devanagari/Hindi + English) text & complex table extraction from scanned Indian PDF gazettes. |
| **Headless Browser** | **Playwright** | Latest | Chromium driver for JavaScript-rendered state/central recruitment portals (NIC, TCS iON, CDAC). |
| **External Orchestrator**| **n8n** | Latest | Outbound distribution (Telegram, WhatsApp, RSS) and external webhooks/discovery bots. |
| **Testing Suite** | **PHPUnit** | **11.x / 12.x** | Robust, standard enterprise unit, feature, and contract testing. |

---

## 3. Core Architectural Principles & System Boundaries

```text
                                ┌───────────────────────────────────────┐
                                │        External Data Sources          │
                                │ (.gov.in, .nic.in, Gazettes, Portals) │
                                └───────────────────┬───────────────────┘
                                                    │
                                                    ▼
                                ┌───────────────────────────────────────┐
                                │    Laravel 13 Scheduler / Queues      │
                                │   (Orchestrator & System of Record)   │
                                └─────────┬───────────────────┬─────────┘
                                          │                   │
                  Dispatch Heavy Ingestion│                   │Direct HTTP Fetch
                                          ▼                   ▼
                     ┌──────────────────────────┐    ┌─────────────────────────┐
                     │ Python Ingestion Sidecar │    │ Laravel HTTP Client     │
                     │  - Playwright Scraping   │    │  (Static HTML / RSS /   │
                     │  - Docling Table Parser  │    │   Direct PDF Downloads) │
                     │  - PaddleOCR (Hindi/Eng) │    └────────────┬────────────┘
                     └────────────┬─────────────┘                 │
                                  │                               │
                                  └───────────────┬───────────────┘
                                                  │
                                                  ▼
                                     ┌─────────────────────────┐
                                     │ Raw Artifact Storage    │
                                     │ (S3 / MinIO / Local FS) │
                                     └────────────┬────────────┘
                                                  │
                                                  ▼
                                     ┌─────────────────────────┐
                                     │  AI Extraction Engine   │
                                     │  (JSON Schema + Ground) │
                                     └────────────┬────────────┘
                                                  │
                                                  ▼
                                     ┌─────────────────────────┐
                                     │ PostgreSQL 17 (Record)  │
                                     │ Notices, Revisions,     │
                                     │ Reservations, Evidence  │
                                     └────────────┬────────────┘
                                                  │
                                    ┌─────────────┴─────────────┐
                                    ▼                           ▼
                        ┌───────────────────────┐   ┌───────────────────────────┐
                        │ Moderation / Admin UI │   │ Outbound Distribution     │
                        │ (Inertia v2+Svelte 5) │   │ (n8n: Telegram, WhatsApp) │
                        └───────────────────────┘   └───────────────────────────┘
```

### 3.1 Principle 1: Laravel is the Sole System of Record
- **PostgreSQL via Laravel** stores authoritative application state, provenance history, verified recruitment records, and user decisions.
- External workers (Python, n8n, AI APIs) **never write directly to PostgreSQL**. They return structured DTOs/payloads back to Laravel via authenticated internal APIs or message queues.

### 3.2 Principle 2: Strict Ingestion Sidecar Boundary
- PHP handles HTTP lifecycle, queuing, caching, and database transactions.
- Complex tabular PDF parsing, Devanagari OCR, and headless browser navigation are executed by the **Python Ingestion Sidecar** (FastAPI).
- Laravel dispatches jobs to the Python sidecar via an internal HTTP API protected by mutual API tokens, or via dedicated Redis queues (`ingestion-ocr`, `ingestion-browser`).

### 3.3 Principle 3: Clear n8n Demarcation
- **Laravel Queues Handle:** Source polling, artifact fetching, OCR invocation, AI extraction validation, entity deduplication, notice revisioning, and publishing.
- **n8n Handles Exclusively:**
  1. Multi-channel outbound broadcasting (pushing newly published notices to Telegram channels, WhatsApp Community APIs, Twitter/X).
  2. Ingestion of third-party external discovery feeds (e.g., scraping aggregators to discover that a new official notification was published).
  3. Escalation alerts to Slack/Discord when critical government portals fail repeatedly.

---

## 4. Standard Laravel 13 Application Blueprint

The AI coding agent must strictly organize the Laravel 13 codebase according to this domain-driven directory structure:

```text
app/
├── Actions/                          # Single-purpose command actions
│   ├── Ingestion/
│   │   ├── FetchSourceArtifactAction.php
│   │   └── RecordArtifactHashAction.php
│   ├── Extraction/
│   │   ├── DispatchOcrAction.php
│   │   ├── ParseRecruitmentTableAction.php
│   │   └── ValidateExtractionGroundingAction.php
│   ├── Notice/
│   │   ├── CreateCandidateNoticeAction.php
│   │   ├── ApplyCorrigendumPatchAction.php
│   │   ├── MergeDuplicateNoticeAction.php
│   │   └── PublishNoticeAction.php
│   └── Moderation/
│       ├── ApproveNoticeAction.php
│       └── RejectNoticeAction.php
├── DTOs/                             # Readonly, strongly-typed data transfer objects
│   ├── Ingestion/
│   │   ├── CrawlConfigDTO.php
│   │   └── RawArtifactDTO.php
│   ├── Extraction/
│   │   ├── ExtractedRecruitmentDTO.php
│   │   ├── PositionDTO.php
│   │   ├── ReservationQuotaDTO.php
│   │   └── GroundedEvidenceDTO.php
│   └── Moderation/
│       └── ModerationDecisionDTO.php
├── Enums/                            # Backed PHP Enums
│   ├── NoticeStatus.php
│   ├── NoticeType.php
│   ├── TrustLevel.php
│   ├── ReservationCategory.php
│   ├── EmploymentType.php
│   └── CorrigendumFieldType.php
├── Events/                           # Domain lifecycle events
│   ├── ArtifactFetched.php
│   ├── NoticeCandidateGenerated.php
│   ├── CorrigendumDetected.php
│   └── NoticePublished.php
├── Jobs/                             # Idempotent queue workers
│   ├── Ingestion/
│   │   ├── CrawlSourceJob.php
│   │   └── ProcessArtifactJob.php
│   ├── Extraction/
│   │   ├── ExecuteOcrJob.php
│   │   └── ExtractRecruitmentFactsJob.php
│   └── Publishing/
│       ├── GeneratePublicPostJob.php
│       └── BroadcastNoticeJob.php
├── Models/                           # Eloquent models (relations, scopes, casts only)
├── Services/                         # Domain services
│   ├── Deduplication/
│   │   ├── MultiLevelDedupService.php
│   │   └── FingerprintService.php
│   ├── Trust/
│   │   └── DomainTrustScorer.php
│   └── Ingestion/
│       └── PythonWorkerClient.php
└── Http/
    ├── Controllers/
    │   ├── Admin/                    # Inertia.js Controllers for Svelte 5 UI
    │   ├── Public/                   # Inertia.js Controllers for Public SEO pages
    │   └── Api/Automation/           # Internal APIs for Ingestion sidecar & n8n
    └── Requests/                     # FormRequests validating all input
```

---

## 5. Frontend Architecture: Inertia v2 + Svelte 5

### 5.1 Monolith Experience via Inertia.js
- Frontend pages reside in `resources/js/Pages/`.
- Shared layout templates in `resources/js/Layouts/` (e.g., `AdminLayout.svelte`, `PublicLayout.svelte`).
- Reusable UI building blocks in `resources/js/Components/` (`EvidenceViewer.svelte`, `SplitDocViewer.svelte`, `ReservationMatrix.svelte`).

### 5.2 Svelte 5 Runes Paradigm
All Svelte code must use modern Svelte 5 Runes:
- Use `$state()` for reactive variables.
- Use `$derived()` for computed states (e.g., total vacancies across categories).
- Use `$props()` for typed component inputs.
- Avoid legacy Svelte 3/4 `export let` or `$: reactive` syntax.

```svelte
<!-- Example: resources/js/Components/VacancyBadge.svelte -->
<script lang="ts">
    interface Props {
        total: number;
        breakdown?: Record<string, number>;
    }

    let { total, breakdown = {} } = $props<Props>();
    let hasBreakdown = $derived(Object.keys(breakdown).length > 0);
</script>

<div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
    <span>Total Vacancies: {total.toLocaleString('en-IN')}</span>
</div>
```

---

## 6. Local Development Environment: Laravel Herd

Local development for **Suchak** is standardized on **Laravel Herd** (Windows / macOS) to eliminate Docker container latency for PHP and frontend asset compilation:

### 6.1 Native Runtime Topology
- **Web App URL:** `https://suchak.test` (managed automatically via `herd link suchak`).
- **PHP Engine:** Native PHP 8.4+ managed by Herd with JIT enabled.
- **Node.js & Vite:** Native Node 22 running `npm run dev` with instant Hot Module Replacement (HMR) for Svelte 5.
- **Companion Services via Docker:** While PHP runs natively, ancillary services are spun up via Docker Compose:
  ```bash
  # Start companion dependencies for local development
  docker compose up -d postgres redis minio ingestion-worker n8n
  ```
- **Local Service Endpoints:**
  - PostgreSQL 17: `127.0.0.1:5432` (DB: `suchak_database`)
  - Redis 7.4: `127.0.0.1:6379`
  - MinIO S3 Console: `http://127.0.0.1:9001` (S3 API: `9000`)
  - Python Ingestion Sidecar: `http://127.0.0.1:8001`
  - n8n Workflow Automation: `http://127.0.0.1:5678`

