<?php

namespace Tests\Feature\Console;

use App\Jobs\CrawlSourceJob;
use App\Models\Institution;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CrawlSourcesCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_dispatches_crawl_jobs_for_due_sources(): void
    {
        Queue::fake();

        $institution = Institution::factory()->create();

        // Source due for crawl
        $dueSource = Source::factory()->create([
            'institution_id' => $institution->id,
            'status' => 'active',
            'next_crawl_at' => now()->subMinute(),
        ]);

        // Source not due yet
        $futureSource = Source::factory()->create([
            'institution_id' => $institution->id,
            'status' => 'active',
            'next_crawl_at' => now()->addHours(2),
        ]);

        $this->artisan('suchak:crawl-sources')
            ->assertSuccessful();

        Queue::assertPushed(CrawlSourceJob::class, function ($job) use ($dueSource) {
            return $job->source->id === $dueSource->id;
        });

        Queue::assertNotPushed(CrawlSourceJob::class, function ($job) use ($futureSource) {
            return $job->source->id === $futureSource->id;
        });
    }

    #[Test]
    public function it_targets_a_specific_source_with_option(): void
    {
        Queue::fake();

        $institution = Institution::factory()->create();

        $source1 = Source::factory()->create([
            'institution_id' => $institution->id,
            'status' => 'active',
        ]);

        $source2 = Source::factory()->create([
            'institution_id' => $institution->id,
            'status' => 'active',
        ]);

        $this->artisan("suchak:crawl-sources --source={$source1->id}")
            ->assertSuccessful();

        Queue::assertPushed(CrawlSourceJob::class, function ($job) use ($source1) {
            return $job->source->id === $source1->id;
        });

        Queue::assertNotPushed(CrawlSourceJob::class, function ($job) use ($source2) {
            return $job->source->id === $source2->id;
        });
    }
}
