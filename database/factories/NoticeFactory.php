<?php

namespace Database\Factories;

use App\Enums\NoticeStatus;
use App\Enums\NoticeType;
use App\Models\Institution;
use App\Models\Notice;
use App\Models\Source;
use App\Models\SourceArtifact;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Notice>
 */
class NoticeFactory extends Factory
{
    protected $model = Notice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = 'Recruitment for '.fake()->jobTitle().' Examination '.date('Y');

        return [
            'institution_id' => Institution::factory(),
            'source_id' => Source::factory(),
            'source_artifact_id' => SourceArtifact::factory(),
            'notice_type' => NoticeType::Recruitment,
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->randomNumber(5),
            'reference_number' => 'ADVT/'.date('Y').'/'.fake()->randomNumber(4),
            'summary' => fake()->paragraph(),
            'status' => NoticeStatus::PendingReview,
            'confidence_score' => 0.8800,
            'is_corrigendum' => false,
            'parent_notice_id' => null,
            'published_at' => now()->subDays(5),
            'application_start_at' => now()->subDays(2),
            'application_end_at' => now()->addDays(20),
            'fee_payment_end_at' => now()->addDays(21),
            'correction_window_end_at' => now()->addDays(23),
            'tentative_exam_date_text' => 'December '.date('Y'),
            'exam_start_at' => now()->addDays(60),
            'exam_end_at' => now()->addDays(62),
            'canonical_source_url' => 'https://upsc.gov.in/examinations/notice-'.fake()->randomNumber(4),
            'total_vacancies' => 100,
            'is_featured' => false,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
            'metadata' => [],
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => NoticeStatus::Approved,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => NoticeStatus::Published,
        ]);
    }
}
