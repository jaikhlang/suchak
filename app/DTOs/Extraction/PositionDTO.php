<?php

namespace App\DTOs\Extraction;

use App\Enums\EmploymentType;

readonly class PositionDTO
{
    /**
     * @param  array<int, ReservationQuotaDTO>  $reservations
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $title,
        public int $totalVacancies,
        public ?string $postCode = null,
        public ?string $department = null,
        public EmploymentType $employmentType = EmploymentType::Permanent,
        public ?string $payLevel = null,
        public ?string $payScaleText = null,
        public ?int $salaryMin = null,
        public ?int $salaryMax = null,
        public array $reservations = [],
        public array $metadata = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $employmentType = isset($data['employment_type'])
            ? (is_string($data['employment_type']) ? EmploymentType::from($data['employment_type']) : $data['employment_type'])
            : EmploymentType::Permanent;

        $reservations = array_map(
            fn ($item) => $item instanceof ReservationQuotaDTO ? $item : ReservationQuotaDTO::fromArray($item),
            $data['reservations'] ?? []
        );

        return new self(
            title: $data['title'],
            totalVacancies: (int) ($data['total_vacancies'] ?? 0),
            postCode: $data['post_code'] ?? null,
            department: $data['department'] ?? null,
            employmentType: $employmentType,
            payLevel: $data['pay_level'] ?? null,
            payScaleText: $data['pay_scale_text'] ?? null,
            salaryMin: isset($data['salary_min']) ? (int) $data['salary_min'] : null,
            salaryMax: isset($data['salary_max']) ? (int) $data['salary_max'] : null,
            reservations: $reservations,
            metadata: $data['metadata'] ?? [],
        );
    }
}
