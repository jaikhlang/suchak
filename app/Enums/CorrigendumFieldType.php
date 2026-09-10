<?php

namespace App\Enums;

enum CorrigendumFieldType: string
{
    case ApplicationEndAt = 'application_end_at';
    case ApplicationStartAt = 'application_start_at';
    case TotalVacancies = 'total_vacancies';
    case FeePaymentEndAt = 'fee_payment_end_at';
    case ExamDate = 'exam_date';
    case EligibilityAge = 'eligibility_age';
    case EligibilityQualification = 'eligibility_qualification';
    case ReservationBreakdown = 'reservation_breakdown';
    case GeneralTextPatch = 'general_text_patch';

    public function label(): string
    {
        return match ($this) {
            self::ApplicationEndAt => 'Application Closing Date',
            self::ApplicationStartAt => 'Application Start Date',
            self::TotalVacancies => 'Total Vacancy Count',
            self::FeePaymentEndAt => 'Fee Payment Deadline',
            self::ExamDate => 'Examination Date / Schedule',
            self::EligibilityAge => 'Age Limit / Cutoff Date',
            self::EligibilityQualification => 'Educational Qualification Criteria',
            self::ReservationBreakdown => 'Category / Quota Breakdown',
            self::GeneralTextPatch => 'General Text Amendment',
        };
    }
}
