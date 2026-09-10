<?php

namespace App\DTOs\Extraction;

readonly class GroundedEvidenceDTO
{
    /**
     * @param  array<string, float|int>|null  $boundingBox
     */
    public function __construct(
        public string $fieldName,
        public string $extractedValue,
        public string $verbatimTextFragment,
        public int $pageNumber = 1,
        public float $confidence = 1.0,
        public ?array $boundingBox = null,
        public ?int $charStartOffset = null,
        public ?int $charEndOffset = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fieldName: $data['field_name'] ?? $data['fieldName'],
            extractedValue: (string) ($data['extracted_value'] ?? $data['extractedValue']),
            verbatimTextFragment: (string) ($data['verbatim_text_fragment'] ?? $data['verbatimTextFragment']),
            pageNumber: (int) ($data['page_number'] ?? $data['pageNumber'] ?? 1),
            confidence: (float) ($data['confidence'] ?? 1.0),
            boundingBox: $data['bounding_box'] ?? $data['boundingBox'] ?? null,
            charStartOffset: $data['char_start_offset'] ?? $data['charStartOffset'] ?? null,
            charEndOffset: $data['char_end_offset'] ?? $data['charEndOffset'] ?? null,
        );
    }
}
