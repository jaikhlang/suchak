<?php

namespace App\Actions\Extraction;

use App\DTOs\Extraction\ExtractedRecruitmentDTO;
use App\Enums\NoticeStatus;
use App\Enums\TrustLevel;
use App\Models\ApplicationDetail;
use App\Models\ArtifactExtraction;
use App\Models\EligibilityRule;
use App\Models\Evidence;
use App\Models\Notice;
use App\Models\Position;
use App\Models\PositionReservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExtractStructuredNoticeAction
{
    public function __construct(
        protected ValidateExtractionGroundingAction $groundingValidator,
        protected ComputeConfidenceScoreAction $confidenceCalculator,
        protected DeduplicateNoticeAction $deduplicator
    ) {}

    public function execute(ArtifactExtraction $extraction, ExtractedRecruitmentDTO $dto): Notice
    {
        $artifact = $extraction->artifact;
        $source = $artifact->source;
        $institution = $source->institution;

        // 1. Verbatim Grounding Validation
        $documentText = (string) ($extraction->clean_text ?: $extraction->raw_text);
        $groundingResult = $this->groundingValidator->execute($dto, $documentText);

        // 2. Compute Multi-Dimensional Confidence Score
        $confidenceScore = $this->confidenceCalculator->execute(
            $dto,
            $groundingResult,
            $source,
            (float) ($extraction->confidence_score ?? 0.95)
        );

        // 3. Deduplication Check
        $dedup = $this->deduplicator->execute(
            institutionId: $institution->id,
            referenceNumber: $dto->referenceNumber,
            canonicalUrl: $artifact->canonical_url,
            isCorrigendum: $dto->isCorrigendum,
            corrigendumParentRef: $dto->corrigendumParentRef
        );

        // 4. Determine Initial Notice Status
        $hasGroundingFailures = ! $groundingResult->isSuccessful();
        $isOfficialVerified = $source->trust_level === TrustLevel::OfficialVerified;

        if ($dedup['is_duplicate']) {
            $status = NoticeStatus::PendingReview;
        } elseif ($hasGroundingFailures || $confidenceScore < 0.94 || ! $isOfficialVerified) {
            $status = NoticeStatus::PendingReview;
        } else {
            $status = NoticeStatus::Approved;
        }

        // 5. Total vacancies aggregation
        $totalVacancies = 0;
        foreach ($dto->positions as $pos) {
            $totalVacancies += $pos->totalVacancies;
        }

        $metadata = array_merge($dto->metadata, [
            'dedup_result' => $dedup,
            'grounding_failures' => $hasGroundingFailures ? $groundingResult->getUnfoundedFields() : [],
            'hallucination_warning' => $hasGroundingFailures,
        ]);

        return DB::transaction(function () use ($artifact, $source, $institution, $extraction, $dto, $status, $confidenceScore, $totalVacancies, $dedup, $metadata) {
            $slugBase = Str::slug($dto->title ?: 'recruitment-notice');
            $slug = "{$institution->slug}-{$slugBase}-".strtolower(Str::random(6));

            $notice = Notice::create([
                'institution_id' => $institution->id,
                'source_id' => $source->id,
                'source_artifact_id' => $artifact->id,
                'notice_type' => $dto->noticeType,
                'title' => $dto->title,
                'slug' => $slug,
                'reference_number' => $dto->referenceNumber,
                'summary' => $dto->metadata['summary'] ?? null,
                'status' => $status,
                'confidence_score' => $confidenceScore,
                'is_corrigendum' => $dto->isCorrigendum,
                'parent_notice_id' => $dedup['parent_notice_id'],
                'published_at' => $dto->dates['published_at'] ?? null,
                'application_start_at' => $dto->dates['application_start_at'] ?? null,
                'application_end_at' => $dto->dates['application_end_at'] ?? null,
                'fee_payment_end_at' => $dto->dates['fee_payment_end_at'] ?? null,
                'correction_window_end_at' => $dto->dates['correction_window_end_at'] ?? null,
                'tentative_exam_date_text' => $dto->dates['exam_date_text'] ?? null,
                'exam_start_at' => $dto->dates['exam_start_at'] ?? null,
                'exam_end_at' => $dto->dates['exam_end_at'] ?? null,
                'canonical_source_url' => $artifact->canonical_url,
                'total_vacancies' => $totalVacancies,
                'is_featured' => false,
                'first_seen_at' => now(),
                'last_seen_at' => now(),
                'metadata' => $metadata,
            ]);

            // Save Positions & Reservation Quotas
            foreach ($dto->positions as $posDto) {
                $position = Position::create([
                    'notice_id' => $notice->id,
                    'title' => $posDto->title,
                    'post_code' => $posDto->postCode,
                    'department' => $posDto->department,
                    'total_vacancies' => $posDto->totalVacancies,
                    'employment_type' => $posDto->employmentType,
                    'pay_level' => $posDto->payLevel,
                    'pay_scale_text' => $posDto->payScaleText,
                    'salary_min' => $posDto->salaryMin,
                    'salary_max' => $posDto->salaryMax,
                    'metadata' => $posDto->metadata,
                ]);

                foreach ($posDto->reservations as $resDto) {
                    PositionReservation::create([
                        'position_id' => $position->id,
                        'category' => $resDto->category,
                        'quota_type' => $resDto->quotaType,
                        'vacancies' => $resDto->vacancies,
                    ]);
                }
            }

            // Save Eligibility Rules
            if (! empty($dto->eligibility)) {
                EligibilityRule::create([
                    'notice_id' => $notice->id,
                    'minimum_age' => $dto->eligibility['minimum_age'] ?? null,
                    'maximum_age' => $dto->eligibility['maximum_age'] ?? null,
                    'age_calculated_as_on' => $dto->eligibility['age_as_on'] ?? null,
                    'age_relaxation_json' => $dto->eligibility['age_relaxation'] ?? [],
                    'qualification_summary' => $dto->eligibility['qualification_summary'] ?? null,
                    'experience_text' => $dto->eligibility['experience_text'] ?? null,
                    'nationality_text' => $dto->eligibility['nationality_text'] ?? 'Citizen of India',
                    'raw_eligibility_text' => $dto->eligibility['raw_text'] ?? null,
                ]);
            }

            // Save Application Details
            if (! empty($dto->applicationDetails)) {
                ApplicationDetail::create([
                    'notice_id' => $notice->id,
                    'application_mode' => $dto->applicationDetails['application_mode'] ?? 'online',
                    'apply_url' => $dto->applicationDetails['apply_url'] ?? null,
                    'official_notification_pdf_url' => $artifact->url,
                    'general_fee' => $dto->applicationDetails['general_fee'] ?? 0,
                    'reserved_fee' => $dto->applicationDetails['reserved_fee'] ?? 0,
                    'female_fee' => $dto->applicationDetails['female_fee'] ?? 0,
                    'is_exempted_for_sc_st' => (bool) ($dto->applicationDetails['is_exempted_for_sc_st'] ?? false),
                    'is_exempted_for_female' => (bool) ($dto->applicationDetails['is_exempted_for_female'] ?? false),
                    'is_exempted_for_pwbd' => (bool) ($dto->applicationDetails['is_exempted_for_pwbd'] ?? false),
                    'offline_postal_address' => $dto->applicationDetails['offline_postal_address'] ?? null,
                    'postal_pincode' => $dto->applicationDetails['postal_pincode'] ?? null,
                    'instructions' => $dto->applicationDetails['instructions'] ?? null,
                    'metadata' => [],
                ]);
            }

            // Save Evidence Records
            foreach ($dto->evidence as $evidenceDto) {
                Evidence::create([
                    'artifact_id' => $artifact->id,
                    'extraction_id' => $extraction->id,
                    'notice_id' => $notice->id,
                    'field_name' => $evidenceDto->fieldName,
                    'extracted_value' => $evidenceDto->extractedValue,
                    'page_number' => $evidenceDto->pageNumber,
                    'verbatim_text_fragment' => $evidenceDto->verbatimTextFragment,
                    'char_start_offset' => $evidenceDto->charStartOffset,
                    'char_end_offset' => $evidenceDto->charEndOffset,
                    'bounding_box' => $evidenceDto->boundingBox,
                    'confidence_score' => $evidenceDto->confidence,
                    'is_verified_by_human' => false,
                ]);
            }

            return $notice;
        });
    }
}
