<?php

namespace App\DTOs\Ingestion;

readonly class RawArtifactDTO
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $sourceId,
        public string $url,
        public string $canonicalUrl,
        public string $type,
        public string $contentHash,
        public string $mimeType,
        public int $fileSizeBytes,
        public int $httpStatus,
        public string $storageDisk,
        public string $storagePath,
        public ?string $title = null,
        public ?string $etag = null,
        public ?string $lastModifiedHeader = null,
        public array $metadata = [],
    ) {}
}
