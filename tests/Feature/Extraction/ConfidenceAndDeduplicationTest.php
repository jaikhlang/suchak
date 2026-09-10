<?php

namespace Tests\Feature\Extraction;

use App\Actions\Extraction\ComputeConfidenceScoreAction;
use App\Actions\Extraction\DeduplicateNoticeAction;
use App\DTOs\Extraction\ExtractedRecruitmentDTO;
use App\DTOs\Extraction\GroundedEvidenceDTO;
use App\DTOs\Extraction\GroundingResult;
use App\DTOs\Extraction\PositionDTO;
use App\Enums\TrustLevel;
use App\Models\Institution;
use App\Models\Notice;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConfidenceAndDeduplicationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_computes_high_confidence_for_official_source_with_perfect_grounding(): void
    {
        $action = $this->app->make(ComputeConfidenceScoreAction::class);

        $source = Source::factory()->create([
            'domain' => 'upsc.gov.in',
            'trust_level' => TrustLevel::OfficialVerified,
        ]);

        $dto = new ExtractedRecruitmentDTO(
            title: 'Combined Medical Services Examination 2026',
            referenceNumber: '08/2026-CMS',
            positions: [new PositionDTO(title: 'Medical Officer', totalVacancies: 50)],
            evidence: [
                new GroundedEvidenceDTO(
                    fieldName: 'application_end_at',
                    extractedValue: '2026-10-31',
                    verbatimTextFragment: 'Last date: 31/10/2026'
                ),
            ],
            dates: ['application_end_at' => '2026-10-31'],
            applicationDetails: ['application_mode' => 'online']
        );

        $groundingResult = GroundingResult::passed();

        $score = $action->execute($dto, $groundingResult, $source, 0.95);

        // (0.30 * 1.0) + (0.35 * 1.0) + (0.20 * 0.95) + (0.15 * 1.0) = 0.30 + 0.35 + 0.19 + 0.15 = 0.99
        $this->assertGreaterThanOrEqual(0.94, $score);
    }

    #[Test]
    public function it_penalizes_confidence_when_source_is_unverified_and_grounding_fails(): void
    {
        $action = $this->app->make(ComputeConfidenceScoreAction::class);

        $source = Source::factory()->create([
            'domain' => 'randomblog.info',
            'trust_level' => TrustLevel::DiscoveryOnly,
        ]);

        $dto = new ExtractedRecruitmentDTO(
            title: 'Sample Recruitment',
            referenceNumber: null,
            evidence: [
                new GroundedEvidenceDTO(
                    fieldName: 'application_end_at',
                    extractedValue: '2026-10-31',
                    verbatimTextFragment: 'Closing: 31-10-2026'
                ),
            ]
        );

        $groundingResult = GroundingResult::failed('Ungrounded snippet', [
            ['field' => 'application_end_at'],
        ]);

        $score = $action->execute($dto, $groundingResult, $source, 0.50);

        $this->assertLessThan(0.75, $score);
    }

    #[Test]
    public function it_detects_duplicate_by_canonical_url(): void
    {
        $action = $this->app->make(DeduplicateNoticeAction::class);
        $institution = Institution::factory()->create();

        $existingNotice = Notice::factory()->create([
            'institution_id' => $institution->id,
            'canonical_source_url' => 'https://example.gov.in/notices/civil-services-2026.pdf',
        ]);

        $result = $action->execute(
            institutionId: $institution->id,
            referenceNumber: 'NEW/01',
            canonicalUrl: 'https://example.gov.in/notices/civil-services-2026.pdf'
        );

        $this->assertTrue($result['is_duplicate']);
        $this->assertSame($existingNotice->id, $result['matched_notice_id']);
        $this->assertSame('canonical_url_match', $result['reason']);
    }

    #[Test]
    public function it_detects_duplicate_by_normalized_reference_number(): void
    {
        $action = $this->app->make(DeduplicateNoticeAction::class);
        $institution = Institution::factory()->create();

        $existingNotice = Notice::factory()->create([
            'institution_id' => $institution->id,
            'reference_number' => 'ADVT NO. 04/2026/ENG',
        ]);

        $result = $action->execute(
            institutionId: $institution->id,
            referenceNumber: '04-2026-eng',
            canonicalUrl: 'https://example.gov.in/notices/different-url.pdf'
        );

        $this->assertTrue($result['is_duplicate']);
        $this->assertSame($existingNotice->id, $result['matched_notice_id']);
        $this->assertSame('reference_number_match', $result['reason']);
    }

    #[Test]
    public function it_links_corrigendum_to_parent_notice(): void
    {
        $action = $this->app->make(DeduplicateNoticeAction::class);
        $institution = Institution::factory()->create();

        $parentNotice = Notice::factory()->create([
            'institution_id' => $institution->id,
            'reference_number' => '07/2026-ENG',
        ]);

        $result = $action->execute(
            institutionId: $institution->id,
            referenceNumber: '07/2026-ENG-CORR-1',
            canonicalUrl: 'https://example.gov.in/notices/corrigendum-1.pdf',
            isCorrigendum: true,
            corrigendumParentRef: '07/2026-ENG'
        );

        $this->assertFalse($result['is_duplicate']);
        $this->assertSame($parentNotice->id, $result['parent_notice_id']);
    }
}
