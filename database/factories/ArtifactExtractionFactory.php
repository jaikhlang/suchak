<?php

namespace Database\Factories;

use App\Models\ArtifactExtraction;
use App\Models\SourceArtifact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArtifactExtraction>
 */
class ArtifactExtractionFactory extends Factory
{
    protected $model = ArtifactExtraction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'artifact_id' => SourceArtifact::factory(),
            'method' => 'pymupdf_text',
            'status' => 'completed',
            'raw_text' => 'Sample extracted text from PDF notification...',
            'clean_text' => 'Sample extracted text from PDF notification...',
            'page_count' => 4,
            'confidence_score' => 0.9850,
            'processor_name' => 'pymupdf-v1',
            'started_at' => now()->subSeconds(30),
            'finished_at' => now(),
            'metadata' => [],
        ];
    }
}
