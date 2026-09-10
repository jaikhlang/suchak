# Suchak (सूचक) — Recruitment & Vacancy Intelligence Platform

> **Notice for Autonomous AI Agents:** This document is the primary entry point and high-level architectural index for project **Suchak** (सूचक — the notifier/indicator of public vacancies). For deep technical specifications, domain rules, database schemas, and implementation contracts, you **must consult the modular context documents** in the [`docs/context/`](docs/context/) directory referenced throughout this document.

---

## 1. Executive Summary & Vision

**Suchak** (सूचक) is an automation-first, intelligence-driven web application designed to continuously monitor, ingest, parse, verify, moderate, and publish recruitment, vacancy, examination, and career updates from authoritative Indian central government, state government, public-sector, statutory, and educational authorities.

### Core Paradigms
- **Automation-First**: 90%+ of crawling, artifact fetching, tabular PDF parsing, Devanagari OCR, structured fact extraction, and deduplication is automated.
- **Provenance & Verification**: Every extracted recruitment fact (vacancies, reservation quotas, dates, eligibility) is immutably anchored to raw source artifacts and highlighted text evidence.
- **Supervisor Role**: Humans act as high-speed reviewers and exception-handlers rather than manual data-entry operators.
- **Local Dev vs. Production**: Local development runs natively via **Laravel Herd** (`http://suchak.test`); production is containerized with Docker and published to GitHub Container Registry (`ghcr.io`).
- **Separation of Concerns**: **Laravel 13.x** is the sole system of record; an internal **Python Sidecar** handles heavy document intelligence (Docling / PaddleOCR); and **n8n** handles external broadcast channels (Telegram, WhatsApp, RSS).

---

## 2. Pinned Technology Stack Matrix (2026 Production Baseline)

| Layer | Technology | Pinned Version | Primary Responsibility |
|---|---|---|---|
| **Core Framework** | **Laravel** | **13.x** | System of record, business logic, DTO orchestration, Eloquent ORM, event-driven pipeline. |
| **Language Runtime** | **PHP** | **8.4+ / 8.5** | Asymmetric visibility, property hooks, typed properties, high-throughput JIT execution. |
| **Local Development**| **Laravel Herd** | Latest (Win/Mac) | Ultra-fast native local PHP 8.4+ and Node runtime at `http://suchak.test`. |
| **Frontend Framework** | **Inertia.js** | **v2.x** (`@inertiajs/svelte`) | Monolith bridge between Laravel controllers and Svelte components without API boilerplate. |
| **Client UI Engine** | **Svelte** | **5.x** (Runes mode) | Modern reactive UI (`$state`, `$derived`, `$props`) for split-screen verification. |
| **Styling** | **Tailwind CSS** | **v4.x** | CSS-first configuration (`@theme`), zero build overhead. |
| **Component Kit** | **shadcn-svelte** | Latest (`bits-ui`) | Accessible, unstyled primitives customized for dense data tables and split-screen viewers. |
| **Primary Database** | **PostgreSQL** | **17+** | Authoritative data store using ULID keys, native JSONB, GIN indexing, and `pg_trgm`. |
| **Cache & Transient State** | **Redis / Valkey** | **Redis 7.4+ / Valkey 8+** | Named job queues, distributed mutex locks, rate limiting, and ephemeral crawl caches. |
| **Ingestion Sidecar** | **Python** | **3.12+ / 3.13** | FastAPI microservice for Docling table extraction, PaddleOCR bilingual pipeline, Playwright. |
| **OCR Engines** | **PaddleOCR / Docling** | Latest | High-accuracy Hindi (Devanagari) + English bilingual text & complex table extraction. |
| **Containerization** | **Docker & Compose** | Latest | Multi-stage production containerization, role-switched entrypoints, and companion dev services. |
| **Image Registry** | **GitHub GHCR** | `ghcr.io` | Automated multi-arch container builds and distribution via GitHub Actions. |
| **External Automation**| **n8n** | Latest | Multi-channel broadcast (Telegram bot, WhatsApp Community alerts) and external discovery. |
| **Testing Suite** | **PHPUnit** | **11.x / 12.x** | Enterprise unit, feature, and contract testing. |

---

## 3. Modular Specification Suite Directory

Deep technical specifications are partitioned into 9 domain modules. Review the relevant module before writing code or running migrations:

```text
.contexts\
├── context.md                                   # Master Entry Point & System Overview (This file)
├── Dockerfile                                   # Multi-stage production Laravel 13 container
├── docker-compose.yml                           # Local development & staging orchestration
├── docker-compose.prod.yml                      # Production orchestration referencing GHCR images
├── .github\workflows\docker-publish.yml        # CI/CD pipeline building & pushing to ghcr.io
└── docs\context\
    ├── 01-architecture-and-stack.md             # Stack specs, runtime boundaries, Laravel directory layout
    ├── 02-database-schema-and-migrations.md     # Relational schema, ULID types, reservations, aliases, indexes
    ├── 03-ingestion-ocr-and-crawler.md          # Laravel-to-Python sidecar contract, Docling, PaddleOCR, SSRF
    ├── 04-indian-recruitment-domain.md          # Categories (UR/OBC/SC/ST/EWS/PwD), Corrigendum protocol, TLD whitelist
    ├── 05-ai-extraction-and-evidence.md         # JSON Schemas, strict verbatim grounding, hallucination flags
    ├── 06-moderation-and-admin-ui.md            # Split-screen UI, Svelte 5 Inertia components, audit logs
    ├── 07-publishing-seo-and-distribution.md    # Post vs Notice separation, JobPosting Schema.org, n8n broadcast
    ├── 08-development-and-ai-agent-guidelines.md # Strict DOs/DON'Ts, MVP phases 1-10, PHPUnit testing & fixtures
    └── 09-docker-and-deployment.md              # Production Docker topology, GHCR registry, and CI/CD
```

### Quick Module Summaries

* [**Module 01: Architecture & Stack**](docs/context/01-architecture-and-stack.md)
  System topology, runtime boundaries between Laravel, Python Sidecar, and n8n, and standard directory blueprints (`app/Actions/`, `app/DTOs/`, `app/Enums/`, `resources/js/Pages/`).

* [**Module 02: Database Schema & Migrations**](docs/context/02-database-schema-and-migrations.md)
  Full PostgreSQL DDL with ULID primary keys, explicit types, and specialized domain tables: `states`, `districts`, `institution_aliases`, `position_reservations`, `evidence`, and `notice_revisions`.

* [**Module 03: Ingestion, OCR & Crawling Pipeline**](docs/context/03-ingestion-ocr-and-crawler.md)
  Python FastAPI sidecar endpoints, Docling complex table extraction, PaddleOCR bilingual (Devanagari + English) recognition, 5-tier deduplication, and strict SSRF protection.

* [**Module 04: Indian Recruitment Domain Rules**](docs/context/04-indian-recruitment-domain.md)
  Corrigendum / Addendum ("शुद्धिपत्र") automated patching protocol, vertical (UR/OBC/SC/ST/EWS) and horizontal (PwBD/ESM/Women) quota models, age relaxation offsets, fee exemption rules, authoritative `.gov.in`/`.nic.in` TLD whitelisting, and copyright standing under Section 52(1)(q).

* [**Module 05: AI Extraction, Evidence & Anti-Hallucination**](docs/context/05-ai-extraction-and-evidence.md)
  Strict JSON Schemas for extraction payloads, the Verbatim Grounding verification rule (exact substring checking), hallucination detection, and multi-dimensional confidence scoring formulas.

* [**Module 06: Moderation Workbench & Admin UI**](docs/context/06-moderation-and-admin-ui.md)
  Split-screen verification interface (interactive document viewer with visual bounding-box highlights side-by-side with structured editable fact cards), hotkey speed-run mode, and Svelte 5 component architectures.

* [**Module 07: Public Publishing, SEO & Outbound Distribution**](docs/context/07-publishing-seo-and-distribution.md)
  Architectural separation between `notices` (truth) and `posts` (presentation), Schema.org `JobPosting` JSON-LD generation, PostgreSQL full-text search with `pg_trgm` typo tolerance, and n8n webhook broadcasting to Telegram and WhatsApp.

* [**Module 08: Development Guidelines & AI Agent Blueprint**](docs/context/08-development-and-ai-agent-guidelines.md)
  Inviolable negative constraints (STRICT DOs and DON'Ts), the 10-phase MVP build roadmap, PHPUnit testing standards, and production `.env.example` configurations.

* [**Module 09: Docker & Production Deployment**](docs/context/09-docker-and-deployment.md)
  Multi-stage containerization, GitHub Container Registry (`ghcr.io`) pipeline, role-switched entrypoints (`web`, `worker`, `scheduler`), local vs. production orchestration files, and zero-downtime deployment rules.

---

## 4. Universal Ingestion & Processing Lifecycle

Every newly discovered resource moves through this lifecycle across the platform:

```text
1. Discover & Schedule (Laravel Scheduler)
         ↓
2. Fetch & Store Raw Artifact (Laravel SafeHttpClient / S3 Storage / Content Hash)
         ↓
3. Extract Layout & Tables (Python Ingestion Sidecar: Docling / PyMuPDF)
         ↓
4. Run OCR if Scanned (PaddleOCR Bilingual Devanagari + English)
         ↓
5. Classify & Extract Structured Facts (AI JSON Schema Contract)
         ↓
6. Validate Verbatim Grounding (Verify Substring Anchors against Raw Text)
         ↓
7. Deduplicate & Entity Match (5-tier Matching: URL, Hash, Advt No, Vector)
         ↓
8. Evaluate Confidence & Policy:
   ├─► Score ≥ 0.94 & Verified Official Source ──► Auto-Publish Post
   └─► Score < 0.94 or Hallucination Flag ──────► Route to Split-Screen Moderation
         ↓
9. Outbound Broadcast (n8n Webhook: Telegram, WhatsApp, RSS)
```

---

## 5. Golden Rules for Future AI Coding Agents

1. **Do Not Overwrite Historical Truth:** Never delete or overwrite an existing notice when a Corrigendum is published. Always spawn a `notice_revisions` record and link the parent.
2. **Ground Every Fact:** If a date, vacancy count, or qualification cannot be proven with an exact substring match in the document, it must never be auto-published.
3. **Respect Runtime Boundaries:** Keep business logic, queuing, and state persistence inside Laravel. Keep heavy OCR, table parsing, and headless browsing inside the Python sidecar. Keep external social distribution in n8n.
4. **Follow Svelte 5 Runes:** In the frontend, write all reactive code using Svelte 5 Runes (`$state`, `$derived`, `$props`). Do not mix legacy syntax or alternative frameworks.
