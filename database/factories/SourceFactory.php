<?php

namespace Database\Factories;

use App\Enums\CrawlMethod;
use App\Enums\SourceType;
use App\Enums\TrustLevel;
use App\Models\Institution;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Source>
 */
class SourceFactory extends Factory
{
    protected $model = Source::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $domain = fake()->domainName();

        return [
            'institution_id' => Institution::factory(),
            'name' => fake()->company().' Noticeboard',
            'type' => fake()->randomElement(SourceType::cases()),
            'url' => "https://{$domain}/notices",
            'canonical_url' => "https://{$domain}/notices",
            'domain' => $domain,
            'crawl_method' => fake()->randomElement(CrawlMethod::cases()),
            'crawl_frequency_minutes' => 120,
            'status' => 'active',
            'trust_level' => TrustLevel::OfficialVerified,
            'consecutive_failures' => 0,
            'last_crawled_at' => null,
            'next_crawl_at' => now(),
            'last_success_at' => null,
            'last_failure_at' => null,
            'configuration' => [
                'selector' => '.notice-list a',
            ],
            'metadata' => [],
        ];
    }
}
