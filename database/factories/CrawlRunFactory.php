<?php

namespace Database\Factories;

use App\Models\CrawlRun;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CrawlRun>
 */
class CrawlRunFactory extends Factory
{
    protected $model = CrawlRun::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_id' => Source::factory(),
            'status' => 'completed',
            'started_at' => now()->subMinutes(5),
            'finished_at' => now(),
            'items_discovered' => 10,
            'items_fetched' => 10,
            'items_failed' => 0,
            'http_status' => 200,
            'error_code' => null,
            'error_message' => null,
            'metadata' => [],
        ];
    }
}
