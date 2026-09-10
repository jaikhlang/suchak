<?php

namespace App\Actions\Extraction;

use App\DTOs\Extraction\ExtractedRecruitmentDTO;
use App\DTOs\Extraction\GroundingResult;

class ValidateExtractionGroundingAction
{
    /**
     * Execute strict verbatim grounding verification against clean document text.
     */
    public function execute(ExtractedRecruitmentDTO $dto, string $documentCleanText): GroundingResult
    {
        $unfoundedFields = [];

        foreach ($dto->evidence as $evidenceItem) {
            $fragment = trim($evidenceItem->verbatimTextFragment);

            if ($fragment === '' || ! str_contains($documentCleanText, $fragment)) {
                $unfoundedFields[] = [
                    'field' => $evidenceItem->fieldName,
                    'claimed_value' => $evidenceItem->extractedValue,
                    'missing_snippet' => $fragment,
                ];
            }
        }

        if (! empty($unfoundedFields)) {
            return GroundingResult::failed(
                reason: 'Ungrounded assertions detected in extraction payload.',
                unfoundedFields: $unfoundedFields
            );
        }

        return GroundingResult::passed();
    }
}
