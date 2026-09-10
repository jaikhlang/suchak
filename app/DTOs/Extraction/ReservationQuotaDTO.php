<?php

namespace App\DTOs\Extraction;

use App\Enums\QuotaType;
use App\Enums\ReservationCategory;

readonly class ReservationQuotaDTO
{
    public function __construct(
        public ReservationCategory $category,
        public int $vacancies,
        public QuotaType $quotaType = QuotaType::Vertical,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $category = is_string($data['category'])
            ? ReservationCategory::from($data['category'])
            : $data['category'];

        $quotaType = isset($data['quota_type'])
            ? (is_string($data['quota_type']) ? QuotaType::from($data['quota_type']) : $data['quota_type'])
            : $category->defaultQuotaType();

        return new self(
            category: $category,
            vacancies: (int) ($data['vacancies'] ?? 0),
            quotaType: $quotaType,
        );
    }
}
