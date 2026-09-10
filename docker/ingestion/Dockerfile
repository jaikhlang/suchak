# ==============================================================================
# Python 3.12 Ingestion & OCR Sidecar Microservice
# ==============================================================================
FROM python:3.12-slim-bookworm

LABEL org.opencontainers.image.title="Suchak - Ingestion & OCR Sidecar"
LABEL org.opencontainers.image.description="FastAPI service for Suchak (Docling table parsing, PaddleOCR bilingual extraction, Playwright)"
LABEL org.opencontainers.image.source="https://github.com/org/suchak"

ENV PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1 \
    PIP_NO_CACHE_DIR=1 \
    PLAYWRIGHT_BROWSERS_PATH=/ms-playwright

WORKDIR /app

# Install system libraries needed for OpenCV, PaddleOCR, and Playwright
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    curl \
    git \
    libgl1 \
    libglib2.0-0 \
    libgomp1 \
    tesseract-ocr \
    tesseract-ocr-hin \
    && rm -rf /var/lib/apt/lists/*

# Install Python requirements
COPY docker/ingestion/requirements.txt /app/requirements.txt
RUN pip install --upgrade pip setuptools wheel \
    && pip install -r /app/requirements.txt

# Install Playwright Chromium headless driver with system dependencies
RUN playwright install --with-deps chromium

# Copy Python microservice code
COPY python_worker /app/python_worker

# Create non-root user
RUN useradd -m -u 1001 appuser && chown -R appuser:appuser /app /ms-playwright
USER appuser

EXPOSE 8001

HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD curl -f http://127.0.0.1:8001/health || exit 1

CMD ["uvicorn", "python_worker.main:app", "--host", "0.0.0.0", "--port", "8001", "--workers", "2"]
