<?php

namespace App\Services\Crawling;

use App\Actions\Ingestion\RecordArtifactHashAction;
use App\Jobs\ProcessArtifactExtractionJob;
use App\Models\CrawlRun;
use App\Models\Source;
use App\Models\SourceArtifact;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SourceCrawlService
{
    public function __construct(
        protected SafeHttpClient $httpClient,
        protected RecordArtifactHashAction $hashAction,
    ) {}

    /**
     * Execute a crawl run for the given source.
     */
    public function crawl(Source $source): CrawlRun
    {
        $crawlRun = CrawlRun::create([
            'source_id' => $source->id,
            'status' => 'running',
            'started_at' => now(),
            'items_discovered' => 0,
            'items_fetched' => 0,
            'items_failed' => 0,
            'http_status' => null,
            'metadata' => [],
        ]);

        try {
            $response = $this->httpClient->get($source->url);
            $crawlRun->http_status = $response->status();

            if (! $response->successful()) {
                throw new \RuntimeException("Crawl failed with HTTP status {$response->status()}");
            }

            $contentType = strtolower($response->header('Content-Type'));
            $body = $response->body();

            $disk = config('filesystems.default', 'local');
            $year = Carbon::now()->format('Y');

            $path = parse_url($source->url, PHP_URL_PATH);
            $isPdfPath = is_string($path) && str_ends_with(strtolower($path), '.pdf');

            // 1. If the source directly points to a PDF
            if (str_contains($contentType, 'application/pdf') || $isPdfPath) {
                $this->processArtifact(
                    source: $source,
                    crawlRun: $crawlRun,
                    url: $source->url,
                    binary: $body,
                    type: 'pdf',
                    mimeType: 'application/pdf',
                    disk: $disk,
                    year: $year,
                    title: $source->name
                );

                $crawlRun->items_discovered = 1;
            } else {
                // 2. HTML noticeboard: Save noticeboard snapshot as HTML artifact
                $this->processArtifact(
                    source: $source,
                    crawlRun: $crawlRun,
                    url: $source->url,
                    binary: $body,
                    type: 'html',
                    mimeType: 'text/html',
                    disk: $disk,
                    year: $year,
                    title: "Snapshot: {$source->name}"
                );

                // Discover linked PDF files
                $discoveredPdfLinks = $this->extractPdfLinks($body, $source->url);
                $crawlRun->items_discovered = count($discoveredPdfLinks);

                // Fetch discovered artifacts (capped at 10 to respect polite crawling)
                $linksToFetch = array_slice($discoveredPdfLinks, 0, 10);

                foreach ($linksToFetch as $link) {
                    try {
                        $this->fetchAndStoreArtifact($source, $crawlRun, $link['url'], $link['title'], $disk, $year);
                    } catch (Throwable $e) {
                        $crawlRun->items_failed++;
                        Log::warning("Failed to fetch artifact from {$link['url']}: {$e->getMessage()}");
                    }
                }
            }

            $crawlRun->status = 'completed';
            $crawlRun->finished_at = now();
            $crawlRun->save();

            $source->update([
                'last_crawled_at' => now(),
                'last_success_at' => now(),
                'consecutive_failures' => 0,
                'next_crawl_at' => now()->addMinutes($source->crawl_frequency_minutes),
            ]);

            return $crawlRun;
        } catch (Throwable $e) {
            $crawlRun->status = 'failed';
            $crawlRun->finished_at = now();
            $crawlRun->error_code = 'CRAWL_EXCEPTION';
            $crawlRun->error_message = $e->getMessage();
            $crawlRun->save();

            $source->update([
                'last_crawled_at' => now(),
                'last_failure_at' => now(),
                'consecutive_failures' => $source->consecutive_failures + 1,
                'next_crawl_at' => now()->addMinutes(min(1440, $source->crawl_frequency_minutes * 2)),
            ]);

            Log::error("Crawl error for source {$source->id} ({$source->name}): {$e->getMessage()}", [
                'source_id' => $source->id,
                'exception' => $e,
            ]);

            return $crawlRun;
        }
    }

    /**
     * Process a raw artifact binary, deduplicate by SHA-256, and persist to storage and database.
     */
    protected function processArtifact(
        Source $source,
        CrawlRun $crawlRun,
        string $url,
        string $binary,
        string $type,
        string $mimeType,
        string $disk,
        string $year,
        ?string $title = null
    ): ?SourceArtifact {
        $hash = $this->hashAction->computeHash($binary);

        // Tier 2 deduplication: Check if identical content hash exists for this source
        $existing = SourceArtifact::where('source_id', $source->id)
            ->where('content_hash', $hash)
            ->first();

        if ($existing) {
            return $existing;
        }

        $extension = $type === 'pdf' ? 'pdf' : 'html';
        $storagePath = "artifacts/sources/{$source->id}/{$year}/{$hash}.{$extension}";

        Storage::disk($disk)->put($storagePath, $binary);

        $artifact = SourceArtifact::create([
            'source_id' => $source->id,
            'crawl_run_id' => $crawlRun->id,
            'type' => $type,
            'url' => $url,
            'canonical_url' => $this->canonicalizeUrl($url),
            'content_hash' => $hash,
            'mime_type' => $mimeType,
            'http_status' => 200,
            'storage_disk' => $disk,
            'storage_path' => $storagePath,
            'file_size_bytes' => strlen($binary),
            'title' => $title,
            'retrieved_at' => now(),
            'metadata' => [],
        ]);

        $crawlRun->items_fetched++;

        ProcessArtifactExtractionJob::dispatch($artifact);

        return $artifact;
    }

    /**
     * Fetch a linked artifact and store it.
     */
    protected function fetchAndStoreArtifact(
        Source $source,
        CrawlRun $crawlRun,
        string $url,
        ?string $title,
        string $disk,
        string $year
    ): ?SourceArtifact {
        $canonicalUrl = $this->canonicalizeUrl($url);

        // Check canonical URL match first (Tier 1 deduplication)
        $existing = SourceArtifact::where('source_id', $source->id)
            ->where('canonical_url', $canonicalUrl)
            ->first();

        if ($existing) {
            return $existing;
        }

        $response = $this->httpClient->get($url);

        if (! $response->successful()) {
            $crawlRun->items_failed++;

            return null;
        }

        $headerType = $response->header('Content-Type');
        $mimeType = ! empty($headerType) ? $headerType : 'application/pdf';

        return $this->processArtifact(
            source: $source,
            crawlRun: $crawlRun,
            url: $url,
            binary: $response->body(),
            type: 'pdf',
            mimeType: $mimeType,
            disk: $disk,
            year: $year,
            title: $title
        );
    }

    /**
     * Extract PDF hyperlinks from HTML markup and resolve them to absolute URLs.
     *
     * @return array<int, array{url: string, title: string|null}>
     */
    protected function extractPdfLinks(string $html, string $baseUrl): array
    {
        $links = [];
        $dom = new \DOMDocument;

        // Suppress HTML5 parsing warnings for malformed government portals
        @$dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);

        $anchors = $dom->getElementsByTagName('a');

        foreach ($anchors as $anchor) {
            $href = trim($anchor->getAttribute('href'));

            if (empty($href) || str_starts_with($href, '#') || str_starts_with($href, 'javascript:')) {
                continue;
            }

            if (str_contains(strtolower($href), '.pdf')) {
                $absoluteUrl = $this->resolveRelativeUrl($href, $baseUrl);
                $title = trim($anchor->textContent);

                if (! empty($absoluteUrl)) {
                    $links[] = [
                        'url' => $absoluteUrl,
                        'title' => ! empty($title) ? $title : null,
                    ];
                }
            }
        }

        // Deduplicate links by resolved URL
        $unique = [];
        foreach ($links as $item) {
            $unique[$item['url']] = $item;
        }

        return array_values($unique);
    }

    /**
     * Resolve a relative URI against a base URL.
     */
    protected function resolveRelativeUrl(string $rel, string $base): string
    {
        if (parse_url($rel, PHP_URL_SCHEME) !== null) {
            return $rel;
        }

        $baseParts = parse_url($base);
        $scheme = $baseParts['scheme'] ?? 'https';
        $host = $baseParts['host'] ?? '';
        $port = isset($baseParts['port']) ? ":{$baseParts['port']}" : '';

        if (str_starts_with($rel, '//')) {
            return "{$scheme}:{$rel}";
        }

        if (str_starts_with($rel, '/')) {
            return "{$scheme}://{$host}{$port}{$rel}";
        }

        $basePath = $baseParts['path'] ?? '/';
        $dir = rtrim(dirname($basePath), '/');

        return "{$scheme}://{$host}{$port}{$dir}/{$rel}";
    }

    /**
     * Canonicalize URL per Module 03 rules:
     * - Strip tracking parameters (utm_*, fbclid, sessionid, ref)
     * - Normalize scheme to https
     * - Lowercase domain
     * - Remove trailing slash on non-root paths
     */
    public function canonicalizeUrl(string $url): string
    {
        $parts = parse_url($url);

        if (! isset($parts['host'])) {
            return $url;
        }

        $scheme = 'https';
        $host = strtolower($parts['host']);
        $port = isset($parts['port']) && ! in_array((int) $parts['port'], [80, 443]) ? ":{$parts['port']}" : '';
        $path = $parts['path'] ?? '/';

        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        $query = '';
        if (isset($parts['query'])) {
            parse_str($parts['query'], $queryParams);

            // Strip tracking parameters
            $filtered = array_filter($queryParams, function ($key) {
                return ! preg_match('/^(utm_|fbclid|sessionid|ref)/i', (string) $key);
            }, ARRAY_FILTER_USE_KEY);

            if (! empty($filtered)) {
                ksort($filtered);
                $query = '?'.http_build_query($filtered);
            }
        }

        return "{$scheme}://{$host}{$port}{$path}{$query}";
    }
}
