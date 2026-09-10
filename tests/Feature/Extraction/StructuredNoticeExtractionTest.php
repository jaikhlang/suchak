<?php

namespace Tests\Feature\Extraction;

use App\Actions\Extraction\ExtractStructuredNoticeAction;
use App\DTOs\Extraction\ExtractedRecruitmentDTO;
use App\DTOs\Extraction\GroundedEvidenceDTO;
use App\DTOs\Extraction\PositionDTO;
use App\DTOs\Extraction\ReservationQuotaDTO;
use App\Enums\NoticeStatus;
use App\Enums\QuotaType;
use App\Enums\ReservationCategory;
use App\Enums\TrustLevel;
use App\Models\ArtifactExtraction;
use App\Models\Institution;
use App\Models\Source;
use App\Models\SourceArtifact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StructuredNoticeExtractionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_persists_notice_positions_and_evidence_when_grounded(): void
    {
        $institution = Institution::factory()->create();
        $source = Source::factory()->create([
            'institution_id' => $institution->id,
            'trust_level' => TrustLevel::OfficialVerified,
            'domain' => 'upsc.gov.in',
        ]);
        $artifact = SourceArtifact::factory()->create([
            'source_id' => $source->id,
        ]);

        $documentText = "UNION PUBLIC SERVICE COMMISSION\nAdvt No: 12/2026\nClosing Date for online submission: 15/11/2026\nTotal Vacancies: 40 Posts for Assistant Director";

        $extraction = ArtifactExtraction::factory()->create([
            'artifact_id' => $artifact->id,
            'clean_text' => $documentText,
            'raw_text' => $documentText,
            'confidence_score' => 0.98,
        ]);

        $dto = new ExtractedRecruitmentDTO(
            title: 'Assistant Director Examination 2026',
            referenceNumber: '12/2026',
            positions: [
                new PositionDTO(
                    title: 'Assistant Director',
                    totalVacancies: 40,
                    reservations: [
                        new ReservationQuotaDTO(
                            category: ReservationCategory::UR,
                            quotaType: QuotaType::Vertical,
                            vacancies: 20
                        ),
                        new ReservationQuotaDTO(
                            category: ReservationCategory::OBC_NCL,
                            quotaType: QuotaType::Vertical,
                            vacancies: 12
                        ),
                    ]
                ),
            ],
            evidence: [
                new GroundedEvidenceDTO(
                    fieldName: 'application_end_at',
                    extractedValue: '2026-11-15',
                    verbatimTextFragment: 'Closing Date for online submission: 15/11/2026',
                    pageNumber: 1
                ),
                new GroundedEvidenceDTO(
                    fieldName: 'total_vacancies',
                    extractedValue: '40',
                    verbatimTextFragment: 'Total Vacancies: 40 Posts',
                    pageNumber: 1
                ),
            ],
            dates: [
                'application_end_at' => '2026-11-15 23:59:59',
            ],
            applicationDetails: [
                'application_mode' => 'online',
                'general_fee' => 200,
            ]
        );

        $action = $this->app->make(ExtractStructuredNoticeAction::class);
        $notice = $action->execute($extraction, $dto);

        $this->assertDatabaseHas('notices', [
            'id' => $notice->id,
            'institution_id' => $institution->id,
            'title' => 'Assistant Director Examination 2026',
            'reference_number' => '12/2026',
            'total_vacancies' => 40,
        ]);

        $this->assertDatabaseHas('positions', [
            'notice_id' => $notice->id,
            'title' => 'Assistant Director',
            'total_vacancies' => 40,
        ]);

        $this->assertDatabaseHas('position_reservations', [
            'category' => 'UR',
            'vacancies' => 20,
        ]);

        $this->assertDatabaseHas('evidence', [
            'notice_id' => $notice->id,
            'field_name' => 'application_end_at',
        ]);

        $this->assertDatabaseHas('application_details', [
            'notice_id' => $notice->id,
            'application_mode' => 'online',
            'general_fee' => 200,
        ]);

        $this->assertSame(NoticeStatus::Approved, $notice->status);
        $this->assertFalse($notice->metadata['hallucination_warning']);
    }

    #[Test]
    public function it_flags_hallucination_and_routes_to_pending_review_when_grounding_fails(): void
    {
        $institution = Institution::factory()->create();
        $source = Source::factory()->create(['institution_id' => $institution->id]);
        $artifact = SourceArtifact::factory()->create(['source_id' => $source->id]);

        $documentText = 'Staff Selection Commission Notice 2026. Only genuine text here.';

        $extraction = ArtifactExtraction::factory()->create([
            'artifact_id' => $artifact->id,
            'clean_text' => $documentText,
            'raw_text' => $documentText,
        ]);

        $dto = new ExtractedRecruitmentDTO(
            title: 'Sub Inspector Recruitment 2026',
            referenceNumber: 'SI/2026',
            positions: [
                new PositionDTO(title: 'Sub Inspector', totalVacancies: 100),
            ],
            evidence: [
                new GroundedEvidenceDTO(
                    fieldName: 'application_end_at',
                    extractedValue: '2026-12-31',
                    verbatimTextFragment: 'Non-existent closing date snippet in document',
                    pageNumber: 1
                ),
            ],
            dates: ['application_end_at' => '2026-12-31']
        );

        $action = $this->app->make(ExtractStructuredNoticeAction::class);
        $notice = $action->execute($extraction, $dto);

        $this->assertSame(NoticeStatus::PendingReview, $notice->status);
        $this->assertTrue($notice->metadata['hallucination_warning']);
        $this->assertCount(1, $notice->metadata['grounding_failures']);
        $this->assertSame('application_end_at', $notice->metadata['grounding_failures'][0]['field']);
    }
}
