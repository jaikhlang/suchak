<?php

namespace Tests\Feature\Admin;

use App\Enums\NoticeStatus;
use App\Enums\UserRole;
use App\Models\Evidence;
use App\Models\Institution;
use App\Models\Notice;
use App\Models\Position;
use App\Models\Source;
use App\Models\SourceArtifact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModerationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->regularUser = User::factory()->create([
            'role' => UserRole::Viewer,
        ]);
    }

    #[Test]
    public function guests_cannot_access_moderation_queue(): void
    {
        $response = $this->get('/admin/moderation');
        $response->assertRedirect('/login');
    }

    #[Test]
    public function regular_candidates_cannot_access_moderation_queue(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/admin/moderation');
        $response->assertForbidden();
    }

    #[Test]
    public function admin_can_view_moderation_queue(): void
    {
        $notice = Notice::factory()->create(['status' => NoticeStatus::PendingReview]);

        $response = $this->actingAs($this->adminUser)->get('/admin/moderation');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/moderation/Index')
            ->has('notices.data', 1)
            ->has('stats')
        );
    }

    #[Test]
    public function admin_can_view_verification_workbench(): void
    {
        $institution = Institution::factory()->create();
        $source = Source::factory()->create(['institution_id' => $institution->id]);
        $artifact = SourceArtifact::factory()->create(['source_id' => $source->id]);

        $notice = Notice::factory()->create([
            'institution_id' => $institution->id,
            'source_id' => $source->id,
            'source_artifact_id' => $artifact->id,
            'status' => NoticeStatus::PendingReview,
        ]);

        Position::factory()->create(['notice_id' => $notice->id]);
        Evidence::factory()->create(['notice_id' => $notice->id, 'artifact_id' => $artifact->id]);

        $response = $this->actingAs($this->adminUser)->get("/admin/moderation/{$notice->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/moderation/Show')
            ->has('notice.id')
            ->has('notice.positions', 1)
            ->has('notice.evidence', 1)
        );
    }

    #[Test]
    public function admin_can_approve_notice_and_generate_public_post(): void
    {
        $notice = Notice::factory()->create(['status' => NoticeStatus::PendingReview]);

        $response = $this->actingAs($this->adminUser)->post("/admin/moderation/{$notice->id}/approve", [
            'notes' => 'Verified against official gazette notification.',
            'publish_post' => true,
        ]);

        $response->assertSessionHas('success');

        $notice->refresh();
        $this->assertSame(NoticeStatus::Published, $notice->status);

        $this->assertDatabaseHas('moderation_reviews', [
            'notice_id' => $notice->id,
            'reviewer_id' => $this->adminUser->id,
            'action' => 'approve',
        ]);

        $this->assertDatabaseHas('posts', [
            'notice_id' => $notice->id,
            'status' => 'published',
        ]);
    }

    #[Test]
    public function admin_can_reject_notice_with_reason(): void
    {
        $notice = Notice::factory()->create(['status' => NoticeStatus::PendingReview]);

        $response = $this->actingAs($this->adminUser)->post("/admin/moderation/{$notice->id}/reject", [
            'reason' => 'duplicate',
            'notes' => 'Already published in circular #04.',
        ]);

        $response->assertRedirect('/admin/moderation');

        $notice->refresh();
        $this->assertSame(NoticeStatus::Rejected, $notice->status);

        $this->assertDatabaseHas('moderation_reviews', [
            'notice_id' => $notice->id,
            'reviewer_id' => $this->adminUser->id,
            'action' => 'reject',
        ]);
    }

    #[Test]
    public function admin_can_apply_manual_field_edits_and_create_revision_diff(): void
    {
        $notice = Notice::factory()->create([
            'status' => NoticeStatus::PendingReview,
            'total_vacancies' => 50,
            'reference_number' => 'OLD/2026',
        ]);

        Evidence::factory()->create([
            'notice_id' => $notice->id,
            'field_name' => 'total_vacancies',
            'extracted_value' => '50',
            'is_verified_by_human' => false,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->from("/admin/moderation/{$notice->id}")
            ->put("/admin/moderation/{$notice->id}", [
                'total_vacancies' => 75,
                'reference_number' => 'CORR/2026/NEW',
                'reason' => 'Corrected post count per page 4 table 2',
            ]);

        $response->assertSessionHas('success');

        $notice->refresh();
        $this->assertSame(75, $notice->total_vacancies);
        $this->assertSame('CORR/2026/NEW', $notice->reference_number);

        $this->assertDatabaseHas('notice_revisions', [
            'notice_id' => $notice->id,
            'version_number' => 1,
            'change_type' => 'manual_edit',
        ]);

        $this->assertDatabaseHas('evidence', [
            'notice_id' => $notice->id,
            'field_name' => 'total_vacancies',
            'extracted_value' => '75',
            'is_verified_by_human' => true,
        ]);
    }
}
