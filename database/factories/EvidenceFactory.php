<?php

namespace Database\Factories;

use App\Models\ArtifactExtraction;
use App\Models\Evidence;
use App\Models\Notice;
use App\Models\SourceArtifact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evidence>
 */
class EvidenceFactory extends Factory
{
    protected $model = Evidence::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'artifact_id' => SourceArtifact::factory(),
            'extraction_id' => ArtifactExtraction::factory(),
            'notice_id' => Notice::factory(),
            'field_name' => 'application_end_at',
            'extracted_value' => '2026-10-31',
            'page_number' => 1,
            'verbatim_text_fragment' => 'The closing date for submission of application is 31.10.2026',
            'char_start_offset' => 120,
            'char_end_offset' => 180,
            'bounding_box' => ['x1' => 100, 'y1' => 200, 'x2' => 450, 'y2' => 240],
            'confidence_score' => 0.9500,
            'is_verified_by_human' => false,
        ];
    }
}
