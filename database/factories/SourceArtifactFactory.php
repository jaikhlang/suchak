<?php

namespace Database\Factories;

use App\Models\CrawlRun;
use App\Models\Source;
use App\Models\SourceArtifact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SourceArtifact>
 */
class SourceArtifactFactory extends Factory
{
    protected $model = SourceArtifact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hash = hash('sha256', fake()->text(200));

        return [
            'source_id' => Source::factory(),
            'crawl_run_id' => CrawlRun::factory(),
            'type' => 'pdf',
            'url' => 'https://example.gov.in/notices/sample.pdf',
            'canonical_url' => 'https://example.gov.in/notices/sample.pdf',
            'content_hash' => $hash,
            'etag' => '"'.fake()->md5().'"',
            'last_modified_header' => null,
            'mime_type' => 'application/pdf',
            'http_status' => 200,
            'storage_disk' => 'local',
            'storage_path' => "artifacts/test/{$hash}.pdf",
            'file_size_bytes' => 102400,
            'title' => 'Recruitment Notification 2026',
            'retrieved_at' => now(),
            'metadata' => [],
        ];
    }
}
