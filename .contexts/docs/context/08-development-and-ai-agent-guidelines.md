# Module 08: Development Guidelines & AI Agent Blueprint

## 1. Autonomous AI Agent Guardrails & Negative Constraints

Any AI coding assistant working on this codebase must strictly observe these inviolable rules:

### Strict Negative Constraints (DO NOT)
1. **DO NOT invent or hallucinate recruitment facts.** If an extracted document does not explicitly state an application deadline or vacancy count, leave the column `NULL` and flag for review.
2. **DO NOT perform expensive OCR, crawling, or AI parsing synchronously within HTTP web requests.** All ingestion and extraction tasks must be pushed to named Laravel Queues.
3. **DO NOT create "God Controllers" or "God Services".** Keep controllers strictly focused on HTTP negotiation. Place domain mutations inside single-purpose `Action` classes (e.g., `CreateNoticeAction`, `ApplyCorrigendumPatchAction`).
4. **DO NOT mix frontend UI ecosystems.** Do not introduce Blade forms, Livewire components, or React packages. The admin and public UI is strictly **Inertia.js v2 + Svelte 5 (Runes mode) + Tailwind CSS v4**.
5. **DO NOT bypass evidence grounding for automated publishing.** A notice cannot be published automatically unless all core fields have verified verbatim snippets in the raw artifact.
6. **DO NOT write raw SQL migrations that delete or alter production data without zero-downtime rollback capabilities.**
7. **DO NOT store plaintext secrets or API tokens in the database or codebase.**

---

## 2. 10-Phase MVP Implementation Roadmap

To maintain velocity and incremental correctness, build the system in the following strict sequence:

| Phase | Milestone | Deliverables |
|---|---|---|
| **Phase 1** | **Foundation** | Laravel 13.x setup, PostgreSQL 17 connection, Redis queue configuration, Inertia.js v2 + Svelte 5 layout, authentication, role-based access control. |
| **Phase 2** | **Taxonomy & Institutions** | `states`, `districts`, `institutions`, `institution_aliases`, and `categories` migrations, seeders, and admin CRUD. |
| **Phase 3** | **Ingestion & Artifacts** | `sources`, `crawl_runs`, and `source_artifacts`. Safe HTTP client with SSRF blocking, S3 artifact storage, and content hashing. |
| **Phase 4** | **Python Ingestion Sidecar** | FastAPI microservice integration with Docling for table extraction and PaddleOCR for Hindi/English scanned PDFs. |
| **Phase 5** | **Structured AI Extraction** | JSON Schema validator, `ValidateExtractionGroundingAction`, DTO mapping, and confidence score computation. |
| **Phase 6** | **Deduplication Engine** | Multi-level deduplication: Canonical URL, SHA-256 hash, normalized reference number matching, and duplicate candidate linking. |
| **Phase 7** | **Moderation Workbench** | Split-screen verification UI in Svelte 5: Interactive PDF/HTML viewer with SVG bounding-box overlays, editable structured fields, and hotkey actions. |
| **Phase 8** | **Public Publishing & SEO** | `posts` generation, Schema.org `JobPosting` JSON-LD, PostgreSQL full-text search (`tsvector`), dynamic sitemaps, and public candidate UI. |
| **Phase 9** | **Corrigendum Protocol** | Automated detection of amendment circulars, parent notice matching, field diff generation, and notice revision history. |
| **Phase 10** | **Distribution & Alerts** | n8n signed webhook integration, Telegram bot channel broadcast, candidate email alerts, and RSS feeds. |

---

## 3. Testing Strategy (PHPUnit 11.x / 12.x)

Every business action and parser must be covered by automated tests using standard **PHPUnit**:

```php
namespace Tests\Feature\Extraction;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\Extraction\ValidateExtractionGroundingAction;
use App\DTOs\Extraction\ExtractedRecruitmentDTO;
use App\DTOs\Extraction\GroundedEvidenceDTO;

class GroundingValidationTest extends TestCase
{
    #[Test]
    public function it_flags_ungrounded_recruitment_deadline_assertions_as_failure(): void
    {
        $action = $this->app->make(ValidateExtractionGroundingAction::class);
        
        $dto = new ExtractedRecruitmentDTO(
            title: 'Assistant Engineer Recruitment 2026',
            referenceNumber: '05/2026',
            evidence: [
                new GroundedEvidenceDTO(
                    fieldName: 'application_end_at',
                    extractedValue: '2026-10-31',
                    verbatimTextFragment: 'Closing Date: 31st October 2026',
                    pageNumber: 1
                )
            ]
        );

        // Document text containing a conflicting date
        $documentText = "UNION PUBLIC SERVICE COMMISSION... Applications close on 15/10/2026.";

        $result = $action->execute($dto, $documentText);

        $this->assertFalse($result->isSuccessful());
        $this->assertCount(1, $result->getUnfoundedFields());
        $this->assertSame('application_end_at', $result->getUnfoundedFields()[0]['field']);
    }
}
```

### Test Suite Structure
- `tests/Unit/Parsers/`: Date parsing, Indian pay scale parsing, reservation calculations, TLD trust evaluation.
- `tests/Feature/Actions/`: Ingestion, deduplication, corrigendum patching, publishing workflows.
- `tests/Feature/Moderation/`: Permission gates, split-screen data serialization, audit logging.
- `tests/Contract/`: Mock HTTP tests validating Python Ingestion Sidecar endpoints and n8n webhook payloads.

---

## 4. Standard Environment Configuration (`.env.example`)

```dotenv
APP_NAME="Suchak"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://suchak.test

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=suchak_database
DB_USERNAME=postgres
DB_PASSWORD=secret

QUEUE_CONNECTION=redis
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

FILESYSTEM_DISK=s3
AWS_ENDPOINT=http://127.0.0.1:9000
AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadmin
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=suchak-artifacts
AWS_USE_PATH_STYLE_ENDPOINT=true

# Python Ingestion Sidecar (Docling + PaddleOCR)
INGESTION_WORKER_URL=http://127.0.0.1:8001
INGESTION_WORKER_API_KEY=internal-sidecar-secret-token

# External Automation (n8n)
N8N_WEBHOOK_URL=http://127.0.0.1:5678/webhook/notice-published
N8N_WEBHOOK_SECRET=n8n-signature-secret-key

# AI Provider Credentials
AI_EXTRACTION_PROVIDER=gemini # or openai / anthropic
AI_EXTRACTION_MODEL=gemini-2.5-pro
AI_EXTRACTION_API_KEY=
```

---

## 5. Local Development Workflow with Laravel Herd

When developing locally on Windows or macOS:

1. **Serve with Laravel Herd:**
   ```bash
   # Register the site with Herd
   herd link suchak
   # Site will be available instantly at: https://suchak.test
   ```
2. **Start Companion Services via Docker:**
   ```bash
   # Run ancillary dependencies (DB, Redis, MinIO, Python worker, n8n)
   docker compose up -d postgres redis minio ingestion-worker n8n
   ```
3. **Compile Frontend with Vite:**
   ```bash
   # Start Vite HMR server for Svelte 5 + Tailwind v4
   npm run dev
   ```
4. **Run Queue Worker locally:**
   ```bash
   php artisan queue:work redis --queue=high,extraction,ocr,crawl,default
   ```
5. **Run Automated Tests:**
   ```bash
   php artisan test
   ```
