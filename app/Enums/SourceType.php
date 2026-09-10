<?php

namespace App\Enums;

enum SourceType: string
{
    case RecruitmentPortal = 'recruitment_portal';
    case PdfNoticeboard = 'pdf_noticeboard';
    case Rss = 'rss';
    case Sitemap = 'sitemap';

    public function label(): string
    {
        return match ($this) {
            self::RecruitmentPortal => 'Recruitment Portal',
            self::PdfNoticeboard => 'PDF Noticeboard',
            self::Rss => 'RSS Feed',
            self::Sitemap => 'Sitemap XML',
        };
    }
}
