<?php

namespace App\Actions\Extraction;

use App\DTOs\Extraction\ExtractedRecruitmentDTO;
use App\DTOs\Extraction\GroundingResult;
use App\Enums\TrustLevel;
use App\Models\Source;

class ComputeConfidenceScoreAction
{
    /**
     * Compute multi-dimensional confidence score.
     *
     * Confidence = (0.30 * C_source) + (0.35 * C_grounding) + (0.20 * C_ocr) + (0.15 * C_schema)
     */
    public function execute(
        ExtractedRecruitmentDTO $dto,
        GroundingResult $groundingResult,
        ?Source $source = null,
        float $ocrConfidence = 0.95
    ): float {
        // 1. Source Trust Dimension (0.30)
        $cSource = $this->calculateSourceTrustScore($source);

        // 2. Verbatim Grounding Dimension (0.35)
        $cGrounding = $this->calculateGroundingScore($dto, $groundingResult);

        // 3. OCR / Text Clarity Dimension (0.20)
        $cOcr = max(0.0, min(1.0, $ocrConfidence));

        // 4. Schema Completeness Dimension (0.15)
        $cSchema = $this->calculateSchemaCompletenessScore($dto);

        $finalScore = (0.30 * $cSource) + (0.35 * $cGrounding) + (0.20 * $cOcr) + (0.15 * $cSchema);

        return round(max(0.0, min(1.0, $finalScore)), 4);
    }

    protected function calculateSourceTrustScore(?Source $source): float
    {
        if (! $source) {
            return 0.50;
        }

        if ($source->trust_level === TrustLevel::OfficialVerified) {
            return 1.0;
        }

        if ($source->trust_level === TrustLevel::InstitutionalVerified) {
            return 0.85;
        }

        $domain = strtolower($source->domain);
        if (str_ends_with($domain, '.gov.in') || str_ends_with($domain, '.nic.in')) {
            return 1.0;
        }

        if (str_ends_with($domain, '.ac.in') || str_ends_with($domain, '.res.in') || str_ends_with($domain, '.edu.in')) {
            return 0.85;
        }

        return 0.40;
    }

    protected function calculateGroundingScore(ExtractedRecruitmentDTO $dto, GroundingResult $groundingResult): float
    {
        $totalEvidence = count($dto->evidence);
        if ($totalEvidence === 0) {
            return 0.0;
        }

        $unfoundedCount = count($groundingResult->getUnfoundedFields());
        $groundedCount = max(0, $totalEvidence - $unfoundedCount);

        return $groundedCount / $totalEvidence;
    }

    protected function calculateSchemaCompletenessScore(ExtractedRecruitmentDTO $dto): float
    {
        $criticalFields = [
            'title' => ! empty($dto->title),
            'reference_number' => ! empty($dto->referenceNumber),
            'application_end_at' => ! empty($dto->dates['application_end_at']),
            'positions' => ! empty($dto->positions),
            'application_mode' => ! empty($dto->applicationDetails['application_mode']),
        ];

        $presentCount = count(array_filter($criticalFields));

        return $presentCount / count($criticalFields);
    }
}
