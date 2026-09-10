<?php

namespace Tests\Feature\Extraction;

use App\Actions\Extraction\ValidateExtractionGroundingAction;
use App\DTOs\Extraction\ExtractedRecruitmentDTO;
use App\DTOs\Extraction\GroundedEvidenceDTO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GroundingValidationTest extends TestCase
{
    #[Test]
    public function it_flags_ungrounded_recruitment_deadline_assertions_as_failure(): void
    {
        $action = $this->app->make(ValidateExtractionGroundingAction::class);

        $dto = new ExtractedRecruitmentDTO(
            title: 'Assistant Engineer Recruitment 2026',
            referenceNumber: '05/2026',
            evidence: [
                new GroundedEvidenceDTO(
                    fieldName: 'application_end_at',
                    extractedValue: '2026-10-31',
                    verbatimTextFragment: 'Closing Date: 31st October 2026',
                    pageNumber: 1
                ),
            ]
        );

        // Document text containing a conflicting date
        $documentText = 'UNION PUBLIC SERVICE COMMISSION... Applications close on 15/10/2026.';

        $result = $action->execute($dto, $documentText);

        $this->assertFalse($result->isSuccessful());
        $this->assertCount(1, $result->getUnfoundedFields());
        $this->assertSame('application_end_at', $result->getUnfoundedFields()[0]['field']);
    }

    #[Test]
    public function it_passes_when_all_evidence_fragments_exist_verbatim_in_document(): void
    {
        $action = $this->app->make(ValidateExtractionGroundingAction::class);

        $dto = new ExtractedRecruitmentDTO(
            title: 'Assistant Engineer Recruitment 2026',
            referenceNumber: '05/2026',
            evidence: [
                new GroundedEvidenceDTO(
                    fieldName: 'application_end_at',
                    extractedValue: '2026-10-31',
                    verbatimTextFragment: 'Closing Date: 31st October 2026',
                    pageNumber: 1
                ),
                new GroundedEvidenceDTO(
                    fieldName: 'total_vacancies',
                    extractedValue: '102',
                    verbatimTextFragment: 'Total Vacancies: 102 Posts',
                    pageNumber: 2
                ),
            ]
        );

        $documentText = 'UNION PUBLIC SERVICE COMMISSION... Total Vacancies: 102 Posts ... Closing Date: 31st October 2026.';

        $result = $action->execute($dto, $documentText);

        $this->assertTrue($result->isSuccessful());
        $this->assertEmpty($result->getUnfoundedFields());
    }
}
