<?php

namespace App\DTOs\Extraction;

use App\Enums\NoticeType;

readonly class ExtractedRecruitmentDTO
{
    /**
     * @param  array<int, PositionDTO>  $positions
     * @param  array<int, GroundedEvidenceDTO>  $evidence
     * @param  array<string, mixed>  $dates
     * @param  array<string, mixed>  $eligibility
     * @param  array<string, mixed>  $applicationDetails
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $title,
        public ?string $referenceNumber = null,
        public NoticeType $noticeType = NoticeType::Recruitment,
        public bool $isCorrigendum = false,
        public ?string $corrigendumParentRef = null,
        public array $positions = [],
        public array $evidence = [],
        public array $dates = [],
        public array $eligibility = [],
        public array $applicationDetails = [],
        public float $overallConfidence = 1.0,
        public array $metadata = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $noticeType = isset($data['notice_type'])
            ? (is_string($data['notice_type']) ? NoticeType::from($data['notice_type']) : $data['notice_type'])
            : NoticeType::Recruitment;

        $positions = array_map(
            fn ($item) => $item instanceof PositionDTO ? $item : PositionDTO::fromArray($item),
            $data['positions'] ?? []
        );

        $evidence = array_map(
            fn ($item) => $item instanceof GroundedEvidenceDTO ? $item : GroundedEvidenceDTO::fromArray($item),
            $data['evidence'] ?? []
        );

        return new self(
            title: $data['title'],
            referenceNumber: $data['reference_number'] ?? $data['referenceNumber'] ?? null,
            noticeType: $noticeType,
            isCorrigendum: (bool) ($data['is_corrigendum'] ?? false),
            corrigendumParentRef: $data['corrigendum_parent_ref'] ?? null,
            positions: $positions,
            evidence: $evidence,
            dates: $data['dates'] ?? [],
            eligibility: $data['eligibility'] ?? [],
            applicationDetails: $data['application_details'] ?? [],
            overallConfidence: (float) ($data['overall_confidence'] ?? 1.0),
            metadata: $data['metadata'] ?? [],
        );
    }
}
