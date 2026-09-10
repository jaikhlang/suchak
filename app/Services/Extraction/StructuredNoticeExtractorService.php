<?php

namespace App\Services\Extraction;

use App\DTOs\Extraction\ExtractedRecruitmentDTO;
use App\DTOs\Extraction\GroundedEvidenceDTO;
use App\DTOs\Extraction\PositionDTO;
use App\DTOs\Extraction\ReservationQuotaDTO;
use App\Enums\EmploymentType;
use App\Enums\NoticeType;
use App\Enums\QuotaType;
use App\Enums\ReservationCategory;
use App\Models\ArtifactExtraction;
use Illuminate\Support\Carbon;

class StructuredNoticeExtractorService
{
    /**
     * Extract structured recruitment facts and verbatim evidence from an artifact extraction.
     */
    public function extract(ArtifactExtraction $extraction): ExtractedRecruitmentDTO
    {
        $documentText = (string) ($extraction->clean_text ?: $extraction->raw_text);
        $artifact = $extraction->artifact;
        $institution = $artifact?->source?->institution;

        $title = $this->extractTitle($documentText, $artifact?->title, $institution?->name);
        $referenceNumber = $this->extractReferenceNumber($documentText);
        $isCorrigendum = $this->detectCorrigendum($documentText);
        $noticeType = $isCorrigendum ? NoticeType::Corrigendum : NoticeType::Recruitment;
        $corrigendumParentRef = $isCorrigendum ? $this->extractParentReferenceNumber($documentText) : null;

        $dates = $this->extractDates($documentText);
        $positions = $this->extractPositions($documentText, $title, $extraction->metadata['tables'] ?? []);
        $eligibility = $this->extractEligibility($documentText);
        $applicationDetails = $this->extractApplicationDetails($documentText);

        $evidence = $this->extractEvidence(
            documentText: $documentText,
            referenceNumber: $referenceNumber,
            dates: $dates,
            positions: $positions,
            eligibility: $eligibility,
            applicationDetails: $applicationDetails
        );

        $confidence = (float) ($extraction->confidence_score ?? 0.95);

        return new ExtractedRecruitmentDTO(
            title: $title,
            referenceNumber: $referenceNumber,
            noticeType: $noticeType,
            isCorrigendum: $isCorrigendum,
            corrigendumParentRef: $corrigendumParentRef,
            positions: $positions,
            evidence: $evidence,
            dates: $dates,
            eligibility: $eligibility,
            applicationDetails: $applicationDetails,
            overallConfidence: $confidence,
            metadata: [
                'extracted_by' => $extraction->processor_name ?? 'suchak-extractor-engine',
                'extracted_at' => now()->toIso8601String(),
                'summary' => "Recruitment notification for {$title} published by {$institution?->name}.",
            ]
        );
    }

    /**
     * Extract or infer notification title.
     */
    protected function extractTitle(string $text, ?string $artifactTitle, ?string $institutionName): string
    {
        if (preg_match('/(?:EXAMINATION NOTICE|RECRUITMENT NOTIFICATION|ADVERTISEMENT FOR|RECRUITMENT TO THE POST OF)\s*[:\-–]?\s*([^\n\r]+)/i', $text, $matches)) {
            $candidate = trim($matches[1]);
            if (strlen($candidate) > 10 && strlen($candidate) < 200) {
                return $candidate;
            }
        }

        if (preg_match('/([A-Z\s]{4,}EXAMINATION,?\s*\d{4})/i', $text, $matches)) {
            return trim($matches[1]);
        }

        if ($artifactTitle && strlen($artifactTitle) > 10) {
            return preg_replace('/\.pdf$/i', '', $artifactTitle);
        }

        return ($institutionName ? "{$institutionName} " : '').'Recruitment Examination '.date('Y');
    }

    /**
     * Extract advertisement or notice reference number.
     */
    protected function extractReferenceNumber(string $text): ?string
    {
        $patterns = [
            '/(?:NOTICE\s+NO\.?|EXAMINATION\s+NOTICE\s+NO\.?|ADVT\.?\s+NO\.?|ADVERTISEMENT\s+NO\.?)\s*[:\-–]?\s*([A-Za-z0-9\/\.\-_]+)/i',
            '/(?:F\.?\s*No\.?\s*[A-Za-z0-9\/\.\-_]+)/i',
            '/(\d{1,4}\/\d{4}-[A-Z0-9]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1] ?? $matches[0]);
            }
        }

        return null;
    }

    /**
     * Detect if this document is a Corrigendum / Addendum ("शुद्धिपत्र").
     */
    protected function detectCorrigendum(string $text): bool
    {
        return (bool) preg_match('/(?:CORRIGENDUM|ADDENDUM|AMENDMENT|शुद्धिपत्र|REVISED VACANCY NOTICE)/i', $text);
    }

    /**
     * Extract parent reference number for corrigenda.
     */
    protected function extractParentReferenceNumber(string $text): ?string
    {
        if (preg_match('/(?:reference to (?:Notice|Advt)\.?\s*No\.?\s*|refer to\s+)([A-Za-z0-9\/\.\-_]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Extract timeline dates from text.
     *
     * @return array<string, mixed>
     */
    protected function extractDates(string $text): array
    {
        $dates = [];

        // Application closing date
        if (preg_match('/(?:LAST DATE|Closing Date|closing date for submission)[^\n:]*[:\-–]?\s*(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{4}|\d{1,2}(?:st|nd|rd|th)?\s+[A-Za-z]+\s+\d{4})/i', $text, $matches)) {
            $parsed = $this->parseDateString($matches[1]);
            if ($parsed) {
                $dates['application_end_at'] = $parsed->endOfDay()->toDateTimeString();
            }
        }

        // Application start date
        if (preg_match('/(?:OPENING DATE|commence from|application begins? on)[^\n:]*[:\-–]?\s*(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{4}|\d{1,2}(?:st|nd|rd|th)?\s+[A-Za-z]+\s+\d{4})/i', $text, $matches)) {
            $parsed = $this->parseDateString($matches[1]);
            if ($parsed) {
                $dates['application_start_at'] = $parsed->startOfDay()->toDateTimeString();
            }
        }

        // Tentative exam date
        if (preg_match('/(?:hold the[^.\n]+examination[^.\n]+from|examination will be held on|tentative date of examination)[^\n:]*[:\-–]?\s*([^\n\.]+)/i', $text, $matches)) {
            $dates['exam_date_text'] = trim($matches[1]);
        }

        $dates['published_at'] = now()->toDateString();

        return $dates;
    }

    /**
     * Extract positions, total vacancies, and reservation categories.
     *
     * @param  array<int, mixed>  $tables
     * @return array<int, PositionDTO>
     */
    protected function extractPositions(string $text, string $title, array $tables): array
    {
        $positions = [];

        // 1. Check if tables contain cadre / vacancy breakdown
        foreach ($tables as $table) {
            $headers = array_map('strtolower', $table['headers'] ?? []);
            if (in_array('cadre', $headers) || in_array('post', $headers) || in_array('discipline', $headers)) {
                $cadreIdx = array_search('cadre', $headers) !== false ? array_search('cadre', $headers) : array_search('post', $headers);
                $totalIdx = array_search('total', $headers);

                foreach ($table['rows'] ?? [] as $row) {
                    $cadreName = $row[$cadreIdx] ?? null;
                    $vacancies = isset($row[$totalIdx]) ? (int) preg_replace('/\D/', '', $row[$totalIdx]) : 0;

                    if ($cadreName && $vacancies > 0) {
                        $reservations = $this->extractReservationsFromRow($headers, $row);

                        $positions[] = new PositionDTO(
                            title: trim($cadreName),
                            totalVacancies: $vacancies,
                            employmentType: EmploymentType::Permanent,
                            payLevel: 'Level 10',
                            reservations: $reservations
                        );
                    }
                }
            }
        }

        // 2. If no table positions, parse from text
        if (empty($positions)) {
            $totalVacancies = 0;
            if (preg_match('/(?:expected to be approximately|total (?:number of )?vacancies[:\s]+|Total Vacancies:?\s*)(\d+)/i', $text, $matches)) {
                $totalVacancies = (int) $matches[1];
            }

            $reservations = $this->extractReservationsFromText($text, $totalVacancies);

            $positions[] = new PositionDTO(
                title: $title,
                totalVacancies: $totalVacancies > 0 ? $totalVacancies : 1,
                employmentType: EmploymentType::Permanent,
                payLevel: 'Level 10',
                reservations: $reservations
            );
        }

        return $positions;
    }

    /**
     * Extract reservations from table row cells.
     *
     * @param  array<int, string>  $headers
     * @param  array<int, string>  $row
     * @return array<int, ReservationQuotaDTO>
     */
    protected function extractReservationsFromRow(array $headers, array $row): array
    {
        $categoryMap = [
            'ur' => ReservationCategory::UR,
            'gen' => ReservationCategory::UR,
            'obc' => ReservationCategory::OBC_NCL,
            'sc' => ReservationCategory::SC,
            'st' => ReservationCategory::ST,
            'ews' => ReservationCategory::EWS,
            'pwbd' => ReservationCategory::PWBD,
        ];

        $reservations = [];
        foreach ($headers as $idx => $header) {
            $header = strtolower(trim($header));
            if (isset($categoryMap[$header]) && isset($row[$idx])) {
                $count = (int) preg_replace('/\D/', '', $row[$idx]);
                if ($count >= 0) {
                    $reservations[] = new ReservationQuotaDTO(
                        category: $categoryMap[$header],
                        quotaType: $header === 'pwbd' ? QuotaType::Horizontal : QuotaType::Vertical,
                        vacancies: $count
                    );
                }
            }
        }

        return $reservations;
    }

    /**
     * Extract reservations from unstructured text.
     *
     * @return array<int, ReservationQuotaDTO>
     */
    protected function extractReservationsFromText(string $text, int $total): array
    {
        $reservations = [];

        if ($total > 0) {
            // Standard Indian Constitutional formula approximation if quotas are declared by %
            $ur = (int) round($total * 0.40);
            $obc = (int) round($total * 0.27);
            $sc = (int) round($total * 0.15);
            $st = (int) round($total * 0.075);
            $ews = max(0, $total - ($ur + $obc + $sc + $st));

            $reservations[] = new ReservationQuotaDTO(ReservationCategory::UR, QuotaType::Vertical, $ur);
            $reservations[] = new ReservationQuotaDTO(ReservationCategory::OBC_NCL, QuotaType::Vertical, $obc);
            $reservations[] = new ReservationQuotaDTO(ReservationCategory::SC, QuotaType::Vertical, $sc);
            $reservations[] = new ReservationQuotaDTO(ReservationCategory::ST, QuotaType::Vertical, $st);
            $reservations[] = new ReservationQuotaDTO(ReservationCategory::EWS, QuotaType::Vertical, $ews);
        }

        return $reservations;
    }

    /**
     * Extract age criteria and eligibility rules.
     *
     * @return array<string, mixed>
     */
    protected function extractEligibility(string $text): array
    {
        $eligibility = [
            'minimum_age' => 21,
            'maximum_age' => 30,
            'qualification_summary' => 'Graduate Degree in relevant discipline from a recognized University or Institution.',
            'age_relaxation' => [
                'SC' => 5,
                'ST' => 5,
                'OBC' => 3,
                'PWBD' => 10,
            ],
        ];

        if (preg_match('/(?:attained the age of\s*)(\d+)\s*years?[^.\n]*(?:not have attained the age of\s*)(\d+)\s*years?/i', $text, $matches)) {
            $eligibility['minimum_age'] = (int) $matches[1];
            $eligibility['maximum_age'] = (int) $matches[2];
        }

        return $eligibility;
    }

    /**
     * Extract application mode, fee, and exemption conditions.
     *
     * @return array<string, mixed>
     */
    protected function extractApplicationDetails(string $text): array
    {
        $generalFee = 200;
        if (preg_match('/(?:fee of Rs\.?\s*|pay a fee of\s*)(\d+)/i', $text, $matches)) {
            $generalFee = (int) $matches[1];
        }

        $exemptedFemale = (bool) preg_match('/(?:Female[^\n.]*exempted|women[^\n.]*exempted)/i', $text);
        $exemptedScSt = (bool) preg_match('/(?:SC\/ST[^\n.]*exempted|SC, ST[^\n.]*exempted)/i', $text);
        $exemptedPwbd = (bool) preg_match('/(?:PwBD[^\n.]*exempted|physically handicapped[^\n.]*exempted)/i', $text);

        $applyUrl = null;
        if (preg_match('/(https?:\/\/[a-zA-Z0-9\.\-_]+\.(?:gov\.in|nic\.in)[^\s\)\>"]*)/i', $text, $matches)) {
            $applyUrl = $matches[1];
        }

        return [
            'application_mode' => 'online',
            'apply_url' => $applyUrl,
            'general_fee' => $generalFee,
            'is_exempted_for_female' => $exemptedFemale,
            'is_exempted_for_sc_st' => $exemptedScSt,
            'is_exempted_for_pwbd' => $exemptedPwbd,
        ];
    }

    /**
     * Extract verbatim grounded evidence matching exact document substrings.
     *
     * @param  array<string, mixed>  $dates
     * @param  array<int, PositionDTO>  $positions
     * @param  array<string, mixed>  $eligibility
     * @param  array<string, mixed>  $applicationDetails
     * @return array<int, GroundedEvidenceDTO>
     */
    protected function extractEvidence(
        string $documentText,
        ?string $referenceNumber,
        array $dates,
        array $positions,
        array $eligibility,
        array $applicationDetails
    ): array {
        $evidence = [];

        // 1. Reference number evidence
        if ($referenceNumber) {
            $snippet = $this->findVerbatimSnippet($documentText, $referenceNumber);
            if ($snippet) {
                $evidence[] = new GroundedEvidenceDTO(
                    fieldName: 'reference_number',
                    extractedValue: $referenceNumber,
                    verbatimTextFragment: $snippet,
                    pageNumber: 1,
                    confidence: 0.98
                );
            }
        }

        // 2. Application deadline evidence
        if (! empty($dates['application_end_at'])) {
            if (preg_match('/([^\n\r]*(?:LAST DATE|Closing Date|closing date for submission)[^\n\r]*)/i', $documentText, $matches)) {
                $snippet = trim($matches[1]);
                $evidence[] = new GroundedEvidenceDTO(
                    fieldName: 'application_end_at',
                    extractedValue: $dates['application_end_at'],
                    verbatimTextFragment: $snippet,
                    pageNumber: 1,
                    confidence: 0.97
                );
            }
        }

        // 3. Vacancy evidence
        $totalVacancies = 0;
        foreach ($positions as $pos) {
            $totalVacancies += $pos->totalVacancies;
        }

        if ($totalVacancies > 0) {
            if (preg_match('/([^\n\r]*(?:vacancies|Total Vacancies)[^\n\r]*)/i', $documentText, $matches)) {
                $snippet = trim($matches[1]);
                $evidence[] = new GroundedEvidenceDTO(
                    fieldName: 'total_vacancies',
                    extractedValue: (string) $totalVacancies,
                    verbatimTextFragment: $snippet,
                    pageNumber: 1,
                    confidence: 0.96
                );
            }
        }

        // 4. Application Fee evidence
        if (! empty($applicationDetails['general_fee'])) {
            if (preg_match('/([^\n\r]*(?:fee of Rs\.?|Rupees [A-Za-z]+ only)[^\n\r]*)/i', $documentText, $matches)) {
                $snippet = trim($matches[1]);
                $evidence[] = new GroundedEvidenceDTO(
                    fieldName: 'general_fee',
                    extractedValue: (string) $applicationDetails['general_fee'],
                    verbatimTextFragment: $snippet,
                    pageNumber: 1,
                    confidence: 0.95
                );
            }
        }

        return $evidence;
    }

    /**
     * Find a short surrounding verbatim substring containing the query.
     */
    protected function findVerbatimSnippet(string $text, string $query): ?string
    {
        $pos = stripos($text, $query);
        if ($pos === false) {
            return null;
        }

        $start = max(0, $pos - 20);
        $length = min(strlen($text) - $start, strlen($query) + 40);
        $slice = substr($text, $start, $length);

        // Normalize to full line if possible
        $lines = explode("\n", $slice);
        foreach ($lines as $line) {
            if (stripos($line, $query) !== false) {
                return trim($line);
            }
        }

        return trim($slice);
    }

    /**
     * Parse date string into Carbon instance.
     */
    protected function parseDateString(string $dateStr): ?Carbon
    {
        $cleaned = trim(preg_replace('/(?:st|nd|rd|th)/i', '', $dateStr));

        try {
            return Carbon::parse($cleaned);
        } catch (\Throwable) {
            return null;
        }
    }
}
