<?php

namespace Tests\Feature\Corrigendum;

use App\Actions\Corrigendum\ApplyCorrigendumPatchAction;
use App\Actions\Publishing\GeneratePublicPostAction;
use App\Enums\NoticeStatus;
use App\Models\Institution;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CorrigendumPatchTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_applies_corrigendum_patch_to_parent_notice_and_creates_revision_diff(): void
    {
        $institution = Institution::factory()->create();
        $user = User::factory()->create();

        $parentNotice = Notice::factory()->create([
            'institution_id' => $institution->id,
            'title' => 'UPSC Combined Geo-Scientist Examination 2026',
            'reference_number' => '02/2026-GEO',
            'status' => NoticeStatus::Published,
            'total_vacancies' => 85,
            'application_end_at' => '2026-10-10 18:00:00',
        ]);

        app(GeneratePublicPostAction::class)->execute($parentNotice);

        $childCorrigendum = Notice::factory()->create([
            'institution_id' => $institution->id,
            'title' => 'Corrigendum - UPSC Combined Geo-Scientist Examination 2026',
            'reference_number' => '02/2026-GEO-CORR-1',
            'status' => NoticeStatus::PendingReview,
            'is_corrigendum' => true,
        ]);

        $patchFields = [
            'total_vacancies' => 95,
            'application_end_at' => '2026-10-25 18:00:00',
        ];

        $action = app(ApplyCorrigendumPatchAction::class);
        $updatedParent = $action->execute(
            parentNotice: $parentNotice,
            childCorrigendum: $childCorrigendum,
            patchFields: $patchFields,
            actor: $user,
            reason: 'Extension of closing date and vacancy increase by Ministry'
        );

        // Verify parent notice patched
        $this->assertSame(95, $updatedParent->total_vacancies);
        $this->assertSame('2026-10-25 18:00:00', $updatedParent->application_end_at->format('Y-m-d H:i:s'));

        // Verify revision audit trail
        $this->assertCount(1, $updatedParent->revisions);
        $revision = $updatedParent->revisions->first();
        $this->assertSame(1, $revision->version_number);
        $this->assertSame('corrigendum_patch', $revision->change_type);
        $this->assertArrayHasKey('total_vacancies', $revision->diff_data);
        $this->assertSame(85, $revision->diff_data['total_vacancies']['old']);
        $this->assertSame(95, $revision->diff_data['total_vacancies']['new']);

        // Verify child linked
        $childCorrigendum->refresh();
        $this->assertSame($parentNotice->id, $childCorrigendum->parent_notice_id);
        $this->assertSame(NoticeStatus::Approved, $childCorrigendum->status);

        // Verify public post updated
        $parentNotice->refresh();
        $this->assertStringContainsString('95', $parentNotice->post->excerpt);
    }
}
