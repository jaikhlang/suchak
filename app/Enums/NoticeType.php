<?php

namespace App\Enums;

enum NoticeType: string
{
    case Recruitment = 'recruitment';
    case Corrigendum = 'corrigendum';
    case Extension = 'extension';
    case ExamDate = 'exam_date';
    case Result = 'result';
    case AdmitCard = 'admit_card';

    public function label(): string
    {
        return match ($this) {
            self::Recruitment => 'Recruitment Notification',
            self::Corrigendum => 'Corrigendum / Addendum (शुद्धिपत्र)',
            self::Extension => 'Deadline Extension Notice',
            self::ExamDate => 'Exam Date / Schedule',
            self::Result => 'Result / Merit List',
            self::AdmitCard => 'Admit Card / Hall Ticket',
        };
    }
}
