<?php

namespace Tests\Feature\Public;

use App\Actions\Publishing\GeneratePublicPostAction;
use App\Enums\NoticeStatus;
use App\Enums\QuotaType;
use App\Enums\ReservationCategory;
use App\Models\ApplicationDetail;
use App\Models\Institution;
use App\Models\Notice;
use App\Models\Position;
use App\Models\PositionReservation;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecruitmentPublicTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_renders_the_home_page_with_stats_and_featured_notices(): void
    {
        $state = State::factory()->create(['name' => 'Delhi', 'iso_code' => 'IN-DL', 'is_active' => true]);
        $institution = Institution::factory()->create([
            'state_id' => $state->id,
            'is_verified' => true,
            'is_active' => true,
            'slug' => 'upsc',
        ]);

        $notice = Notice::factory()->create([
            'institution_id' => $institution->id,
            'status' => NoticeStatus::Published,
            'total_vacancies' => 102,
            'application_end_at' => now()->addDays(20),
        ]);

        app(GeneratePublicPostAction::class)->execute($notice);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->has('featuredPosts', 1)
            ->where('stats.total_vacancies', 102)
            ->where('stats.active_notices', 1)
            ->has('states')
            ->has('topInstitutions')
        );
    }

    #[Test]
    public function it_renders_recruitment_catalog_with_published_posts(): void
    {
        $institution = Institution::factory()->create(['slug' => 'ssc']);
        $notice = Notice::factory()->create([
            'institution_id' => $institution->id,
            'status' => NoticeStatus::Published,
            'title' => 'Combined Graduate Level Examination 2026',
        ]);
        app(GeneratePublicPostAction::class)->execute($notice);

        $response = $this->get('/recruitment');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('recruitment/Index')
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Combined Graduate Level Examination 2026')
        );
    }

    #[Test]
    public function it_filters_recruitment_posts_by_search_term(): void
    {
        $institution = Institution::factory()->create(['slug' => 'rrb']);
        $notice1 = Notice::factory()->create([
            'institution_id' => $institution->id,
            'status' => NoticeStatus::Published,
            'title' => 'Assistant Loco Pilot Recruitment 2026',
        ]);
        $notice2 = Notice::factory()->create([
            'institution_id' => $institution->id,
            'status' => NoticeStatus::Published,
            'title' => 'Junior Engineer Civil 2026',
        ]);

        app(GeneratePublicPostAction::class)->execute($notice1);
        app(GeneratePublicPostAction::class)->execute($notice2);

        $response = $this->get('/recruitment?search=Loco');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('recruitment/Index')
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Assistant Loco Pilot Recruitment 2026')
        );
    }

    #[Test]
    public function it_filters_recruitment_posts_by_state_and_reservation_category(): void
    {
        $state1 = State::factory()->create(['name' => 'Bihar', 'is_active' => true]);
        $state2 = State::factory()->create(['name' => 'Punjab', 'is_active' => true]);

        $inst1 = Institution::factory()->create(['state_id' => $state1->id]);
        $inst2 = Institution::factory()->create(['state_id' => $state2->id]);

        $notice1 = Notice::factory()->create([
            'institution_id' => $inst1->id,
            'status' => NoticeStatus::Published,
            'title' => 'Bihar Police Sub Inspector',
        ]);
        $notice2 = Notice::factory()->create([
            'institution_id' => $inst2->id,
            'status' => NoticeStatus::Published,
            'title' => 'Punjab Revenue Patwari',
        ]);

        $pos1 = Position::factory()->create(['notice_id' => $notice1->id]);
        PositionReservation::factory()->create([
            'position_id' => $pos1->id,
            'category' => ReservationCategory::OBC_NCL,
            'quota_type' => QuotaType::Vertical,
            'vacancies' => 25,
        ]);

        app(GeneratePublicPostAction::class)->execute($notice1);
        app(GeneratePublicPostAction::class)->execute($notice2);

        // State filter
        $responseState = $this->get("/recruitment?state_id={$state1->id}");
        $responseState->assertOk();
        $responseState->assertInertia(fn ($page) => $page
            ->component('recruitment/Index')
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Bihar Police Sub Inspector')
        );

        // Category filter
        $responseCat = $this->get('/recruitment?category=obc_ncl');
        $responseCat->assertOk();
        $responseCat->assertInertia(fn ($page) => $page
            ->component('recruitment/Index')
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Bihar Police Sub Inspector')
        );
    }

    #[Test]
    public function it_renders_single_recruitment_post_with_job_posting_schema_org_json(): void
    {
        $institution = Institution::factory()->create([
            'slug' => 'upsc',
            'name' => 'Union Public Service Commission',
        ]);

        $notice = Notice::factory()->create([
            'institution_id' => $institution->id,
            'status' => NoticeStatus::Published,
            'title' => 'Engineering Services Examination 2026',
            'total_vacancies' => 100,
            'application_end_at' => now()->addDays(30),
        ]);

        ApplicationDetail::factory()->create([
            'notice_id' => $notice->id,
            'general_fee' => 200,
            'apply_url' => 'https://upsconline.nic.in',
        ]);

        $post = app(GeneratePublicPostAction::class)->execute($notice);

        $response = $this->get("/recruitment/{$institution->slug}/{$post->slug}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('recruitment/Show')
            ->where('post.slug', $post->slug)
            ->where('schemaJson.@type', 'JobPosting')
            ->where('schemaJson.title', 'Engineering Services Examination 2026')
            ->where('schemaJson.totalJobOpenings', 100)
            ->where('schemaJson.hiringOrganization.name', 'Union Public Service Commission')
        );
    }

    #[Test]
    public function it_generates_valid_googlebot_xml_sitemap(): void
    {
        $institution = Institution::factory()->create(['slug' => 'upsc']);
        $notice = Notice::factory()->create([
            'institution_id' => $institution->id,
            'status' => NoticeStatus::Published,
            'title' => 'Civil Services Examination 2026',
        ]);
        $post = app(GeneratePublicPostAction::class)->execute($notice);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $response->getContent());
        $this->assertStringContainsString("/recruitment/upsc/{$post->slug}", $response->getContent());
    }

    #[Test]
    public function it_generates_valid_rss_feed(): void
    {
        $institution = Institution::factory()->create([
            'slug' => 'ssc',
            'name' => 'Staff Selection Commission',
        ]);
        $notice = Notice::factory()->create([
            'institution_id' => $institution->id,
            'status' => NoticeStatus::Published,
            'title' => 'SSC CHSL 2026',
            'total_vacancies' => 3500,
        ]);
        $post = app(GeneratePublicPostAction::class)->execute($notice);

        $response = $this->get('/feeds/latest.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/rss+xml; charset=utf-8');
        $content = $response->getContent();
        $this->assertStringContainsString('<rss version="2.0"', $content);
        $this->assertStringContainsString('Project Suchak (सूचक)', $content);
        $this->assertStringContainsString('SSC CHSL 2026', $content);
        $this->assertStringContainsString('Staff Selection Commission (3500 Vacancies)', $content);
    }

    #[Test]
    public function it_generates_valid_state_specific_rss_feed(): void
    {
        $state = State::factory()->create([
            'name' => 'Rajasthan',
            'iso_code' => 'IN-RJ',
            'is_active' => true,
        ]);
        $institution = Institution::factory()->create([
            'state_id' => $state->id,
            'name' => 'Rajasthan Public Service Commission',
            'slug' => 'rpsc',
        ]);
        $notice = Notice::factory()->create([
            'institution_id' => $institution->id,
            'status' => NoticeStatus::Published,
            'title' => 'Rajasthan Administrative Services 2026',
        ]);
        app(GeneratePublicPostAction::class)->execute($notice);

        $response = $this->get('/feeds/state/rajasthan.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/rss+xml; charset=utf-8');
        $content = $response->getContent();
        $this->assertStringContainsString('Recruitment Notifications for Rajasthan', $content);
        $this->assertStringContainsString('Rajasthan Administrative Services 2026', $content);
    }
}
