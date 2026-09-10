<?php

namespace App\DTOs\Extraction;

readonly class GroundingResult
{
    /**
     * @param  array<int, array{field: string, claimed_value: string, missing_snippet: string}>  $unfoundedFields
     */
    public function __construct(
        public bool $success,
        public ?string $reason = null,
        public array $unfoundedFields = [],
    ) {}

    public static function passed(): self
    {
        return new self(success: true);
    }

    /**
     * @param  array<int, array{field: string, claimed_value: string, missing_snippet: string}>  $unfoundedFields
     */
    public static function failed(string $reason, array $unfoundedFields = []): self
    {
        return new self(
            success: false,
            reason: $reason,
            unfoundedFields: $unfoundedFields,
        );
    }

    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * @return array<int, array{field: string, claimed_value: string, missing_snippet: string}>
     */
    public function getUnfoundedFields(): array
    {
        return $this->unfoundedFields;
    }
}
