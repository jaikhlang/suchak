# Module 09: Production Docker & Deployment Architecture — Suchak (सूचक)

## 1. Container Architecture Overview

**Suchak** uses a production-hardened container topology built on Docker, orchestrated via `docker-compose`, and distributed through the **GitHub Container Registry (GHCR)**.

```text
                                  ┌────────────────────────┐
                                  │  GitHub Actions CI/CD  │
                                  └───────────┬────────────┘
                                              │ Builds & Pushes
                                              ▼
                             ┌─────────────────────────────────┐
                             │  GitHub Container Registry      │
                             │  (ghcr.io/<org>/...)            │
                             ├────────────────┬────────────────┤
                             │   suchak-app   │ingestion-worker│
                             └────────┬───────┴────────┬───────┘
                                      │                │
                        docker pull   │                │ docker pull
                                      ▼                ▼
┌──────────────────────────────────────────────────────────────────────────────┐
│ PRODUCTION HOST (docker-compose.prod.yml)                                    │
│                                                                              │
│    ┌──────────────┐          ┌────────────────┐          ┌────────────────┐  │
│    │  app (web)   │          │  queue-worker  │          │   scheduler    │  │
│    │  (Port 80)   │          │  (Queues)      │          │   (Cron)       │  │
│    └──────┬───────┘          └───────┬────────┘          └───────┬────────┘  │
│           │                          │                           │           │
│           └──────────────────────────┼───────────────────────────┘           │
│                                      │                                       │
│                       ┌──────────────┴──────────────┐                        │
│                       ▼                             ▼                        │
│               ┌───────────────┐             ┌───────────────┐                │
│               │ PostgreSQL 17 │             │   Redis 7.4   │                │
│               └───────────────┘             └───────┬───────┘                │
│                                                     │                        │
│                                      ┌──────────────┴──────────────┐         │
│                                      ▼                             ▼         │
│                              ┌───────────────┐             ┌───────────────┐ │
│                              │  ingestion-   │             │   n8n         │ │
│                              │  worker       │             │   (Broadcast) │ │
│                              │  (FastAPI:8001│             │   (Port 5678) │ │
│                              └───────────────┘             └───────────────┘ │
└──────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Container Images in GHCR

| Image Repository | Dockerfile Location | Base Image | Description & Roles |
|---|---|---|---|
| `ghcr.io/<org>/suchak-app` | [`Dockerfile`](file:///d:/project/Dockerfile) | Multi-stage: Node 22 $\rightarrow$ PHP 8.4 Composer $\rightarrow$ PHP 8.4-FPM Alpine | Single image handling 3 distinct roles: **Web** (Nginx + FPM), **Worker** (Queue consumer), or **Scheduler** (Cron daemon). |
| `ghcr.io/<org>/suchak-ingestion-worker` | [`docker/ingestion/Dockerfile`](file:///d:/project/docker/ingestion/Dockerfile) | Python 3.12-slim-bookworm | High-throughput document intelligence service running FastAPI, Docling table parser, PaddleOCR, and Playwright Chromium. |

---

## 3. Container Role Dispatch via `entrypoint.sh`

The core Laravel image dynamically assumes its functional role using the `CONTAINER_ROLE` environment variable configured in [`docker/entrypoint.sh`](file:///d:/project/docker/entrypoint.sh):

```bash
# 1. Web Role (Default)
CONTAINER_ROLE=web
# Runs config/route caches, optional auto-migration, starts PHP-FPM, and execs Nginx on port 80.

# 2. Worker Role
CONTAINER_ROLE=worker
# Starts queue consumer listening to high, extraction, ocr, crawl, and default queues.

# 3. Scheduler Role
CONTAINER_ROLE=scheduler
# Runs the minute-by-minute Laravel scheduler daemon loop.
```

---

## 4. Orchestration Environments

### 4.1 Local Development with Laravel Herd & Companion Containers
- **Web App:** Runs natively on the host machine via **Laravel Herd** at `http://suchak.test` (eliminating Docker overhead for PHP and Vite).
- **Companion Stack (`docker-compose.yml`):** Spun up via Docker to supply PostgreSQL, Redis, MinIO S3, the Python Ingestion worker, and n8n:
  ```bash
  # Start only the companion dependencies for Herd
  docker compose up -d postgres redis minio ingestion-worker n8n
  ```
- **Full Containerized Local Testing:** If testing the entire stack inside Docker:
  ```bash
  docker compose up -d --build
  # View real-time logs
  docker compose logs -f app queue-worker ingestion-worker
  ```

### 4.2 Production Staging & Live (`docker-compose.prod.yml`)
- Pulls signed, optimized immutable images from `ghcr.io`.
- Does not expose database, Redis, or internal sidecar ports to the public internet. Only port 80/443 is exposed behind a reverse proxy (e.g., Caddy, Traefik, or Nginx).
- Enforces container healthchecks on PostgreSQL, Redis, and the Ingestion worker before launching the web app.

```bash
# Deploy to production server
docker compose -f docker-compose.prod.yml pull
docker compose -f docker-compose.prod.yml up -d
```

---

## 5. Automated CI/CD Pipeline (GitHub Actions)

The repository includes [`.github/workflows/docker-publish.yml`](file:///d:/project/.github/workflows/docker-publish.yml) configured with:
1. **GitHub Buildx Caching (`type=gha`):** Drastically cuts build times by caching Node modules, Composer vendors, and Python wheels across pipeline runs.
2. **Semver & SHA Tagging:** Images are automatically tagged with:
   - Branch name (e.g., `main`).
   - Git commit short SHA (e.g., `sha-3a1b2c4`).
   - Semantic versions on Git release tags (`v1.0.0`, `1.0`).
   - `latest` tag on pushes to the `main` branch.
3. **Permissions:** Scoped to `packages: write` and `contents: read` using native `${{ secrets.GITHUB_TOKEN }}`.

---

## 6. Zero-Downtime Deployment & Maintenance Rules

1. **Database Migrations:** Keep migrations backward-compatible. Run `AUTO_MIGRATE=true` only on the `web` container.
2. **Worker Restart on Deploy:** Whenever a new image is deployed, signal queue workers to cleanly finish active jobs and restart:
   ```bash
   docker compose exec app php artisan queue:restart
   ```
3. **OPcache Invalidation:** In production Alpine PHP-FPM, OPcache JIT preloading is enabled (`opcache.validate_timestamps=0`). Any application code updates necessitate a container restart so OPcache re-warms cleanly.
