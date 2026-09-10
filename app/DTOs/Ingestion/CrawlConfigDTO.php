<?php

namespace App\DTOs\Ingestion;

use App\Enums\CrawlMethod;

readonly class CrawlConfigDTO
{
    /**
     * @param  array<string, string>  $headers
     * @param  array<string, mixed>  $selectors
     */
    public function __construct(
        public CrawlMethod $method = CrawlMethod::HttpStatic,
        public int $frequencyMinutes = 120,
        public int $delayMs = 2000,
        public int $maxPages = 5,
        public array $headers = [],
        public array $selectors = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $method = isset($data['method'])
            ? (is_string($data['method']) ? CrawlMethod::from($data['method']) : $data['method'])
            : CrawlMethod::HttpStatic;

        return new self(
            method: $method,
            frequencyMinutes: (int) ($data['frequency_minutes'] ?? $data['frequencyMinutes'] ?? 120),
            delayMs: (int) ($data['delay_ms'] ?? $data['delayMs'] ?? 2000),
            maxPages: (int) ($data['max_pages'] ?? $data['maxPages'] ?? 5),
            headers: $data['headers'] ?? [],
            selectors: $data['selectors'] ?? [],
        );
    }
}
