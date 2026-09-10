<?php

namespace Database\Factories;

use App\Models\ApplicationDetail;
use App\Models\Notice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationDetail>
 */
class ApplicationDetailFactory extends Factory
{
    protected $model = ApplicationDetail::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notice_id' => Notice::factory(),
            'application_mode' => 'online',
            'apply_url' => 'https://example.gov.in/apply',
            'official_notification_pdf_url' => 'https://example.gov.in/notification.pdf',
            'general_fee' => 100,
            'reserved_fee' => 0,
            'female_fee' => 0,
            'is_exempted_for_sc_st' => true,
            'is_exempted_for_female' => true,
            'is_exempted_for_pwbd' => true,
            'offline_postal_address' => null,
            'postal_pincode' => null,
            'instructions' => 'Apply online through the portal before deadline.',
            'metadata' => [],
        ];
    }
}
