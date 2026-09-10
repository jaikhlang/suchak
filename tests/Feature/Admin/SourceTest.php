<?php

namespace Tests\Feature\Admin;

use App\Enums\CrawlMethod;
use App\Enums\QueueName;
use App\Enums\SourceType;
use App\Enums\TrustLevel;
use App\Enums\UserRole;
use App\Jobs\CrawlSourceJob;
use App\Models\Institution;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Institution $institution;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->institution = Institution::factory()->create();
    }

    #[Test]
    public function guests_are_redirected_from_sources(): void
    {
        $response = $this->get(route('admin.sources.index'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function admin_can_view_sources_index(): void
    {
        Source::factory()->count(3)->create(['institution_id' => $this->institution->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.sources.index'));

        $response->assertOk();
    }

    #[Test]
    public function admin_can_create_a_source(): void
    {
        $payload = [
            'institution_id' => $this->institution->id,
            'name' => 'UPSC Active Examinations',
            'type' => SourceType::RecruitmentPortal->value,
            'url' => 'https://upsc.gov.in/examinations/active-exams',
            'canonical_url' => 'https://upsc.gov.in/examinations/active-exams',
            'crawl_method' => CrawlMethod::HttpStatic->value,
            'crawl_frequency_minutes' => 120,
            'status' => 'active',
            'trust_level' => TrustLevel::OfficialVerified->value,
            'configuration' => ['selector' => '.notice-list a'],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.sources.store'), $payload);

        $source = Source::where('name', 'UPSC Active Examinations')->first();
        $this->assertNotNull($source);
        $this->assertSame('upsc.gov.in', $source->domain);

        $response->assertRedirect(route('admin.sources.show', $source));
    }

    #[Test]
    public function admin_can_view_source_show_page(): void
    {
        $source = Source::factory()->create(['institution_id' => $this->institution->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.sources.show', $source));

        $response->assertOk();
    }

    #[Test]
    public function admin_can_update_a_source(): void
    {
        $source = Source::factory()->create(['institution_id' => $this->institution->id]);

        $payload = [
            'institution_id' => $this->institution->id,
            'name' => 'Updated Source Name',
            'type' => SourceType::PdfNoticeboard->value,
            'url' => 'https://updated.gov.in/notices',
            'canonical_url' => 'https://updated.gov.in/notices',
            'crawl_method' => CrawlMethod::HttpStatic->value,
            'crawl_frequency_minutes' => 60,
            'status' => 'paused',
            'trust_level' => TrustLevel::OfficialVerified->value,
            'configuration' => [],
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.sources.update', $source), $payload);

        $response->assertRedirect(route('admin.sources.show', $source));

        $source->refresh();
        $this->assertSame('Updated Source Name', $source->name);
        $this->assertSame('updated.gov.in', $source->domain);
        $this->assertSame('paused', $source->status);
    }

    #[Test]
    public function admin_can_delete_a_source(): void
    {
        $source = Source::factory()->create(['institution_id' => $this->institution->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.sources.destroy', $source));

        $response->assertRedirect(route('admin.sources.index'));
        $this->assertDatabaseMissing('sources', ['id' => $source->id]);
    }

    #[Test]
    public function admin_can_trigger_crawl_now_which_dispatches_job(): void
    {
        Queue::fake();

        $source = Source::factory()->create(['institution_id' => $this->institution->id]);

        $response = $this->actingAs($this->admin)->post(route('admin.sources.crawl', $source));

        $response->assertSessionHas('status');

        Queue::assertPushed(CrawlSourceJob::class, function ($job) use ($source) {
            return $job->source->id === $source->id && $job->queue === QueueName::Crawl->value;
        });
    }
}
