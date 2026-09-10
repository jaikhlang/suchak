<?php

namespace Database\Seeders;

use App\Enums\CrawlMethod;
use App\Enums\SourceType;
use App\Enums\TrustLevel;
use App\Models\Institution;
use App\Models\Source;
use Illuminate\Database\Seeder;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            [
                'institution_slug' => 'upsc',
                'name' => 'UPSC Active Examinations Noticeboard',
                'type' => SourceType::RecruitmentPortal,
                'url' => 'https://upsc.gov.in/examinations/active-exams',
                'canonical_url' => 'https://upsc.gov.in/examinations/active-exams',
                'domain' => 'upsc.gov.in',
                'crawl_method' => CrawlMethod::HttpStatic,
                'crawl_frequency_minutes' => 120,
                'trust_level' => TrustLevel::OfficialVerified,
                'configuration' => [
                    'selector' => '.view-active-examinations tbody tr',
                    'pdf_link_selector' => 'a[href$=".pdf"]',
                ],
            ],
            [
                'institution_slug' => 'ssc',
                'name' => 'SSC Latest Notices Portal',
                'type' => SourceType::RecruitmentPortal,
                'url' => 'https://ssc.gov.in/notices',
                'canonical_url' => 'https://ssc.gov.in/notices',
                'domain' => 'ssc.gov.in',
                'crawl_method' => CrawlMethod::HttpStatic,
                'crawl_frequency_minutes' => 60,
                'trust_level' => TrustLevel::OfficialVerified,
                'configuration' => [
                    'selector' => '.notice-card',
                    'pdf_link_selector' => 'a.download-btn',
                ],
            ],
            [
                'institution_slug' => 'uppsc',
                'name' => 'UPPSC All Notifications Portal',
                'type' => SourceType::PdfNoticeboard,
                'url' => 'https://uppsc.up.nic.in/AllNotifications.aspx',
                'canonical_url' => 'https://uppsc.up.nic.in/AllNotifications.aspx',
                'domain' => 'uppsc.up.nic.in',
                'crawl_method' => CrawlMethod::HttpStatic,
                'crawl_frequency_minutes' => 120,
                'trust_level' => TrustLevel::OfficialVerified,
                'configuration' => [
                    'selector' => '#DataGrid1 tr',
                    'pdf_link_selector' => 'a[href*=".pdf"]',
                ],
            ],
            [
                'institution_slug' => 'ibps',
                'name' => 'IBPS Current Openings',
                'type' => SourceType::RecruitmentPortal,
                'url' => 'https://ibps.in/careers',
                'canonical_url' => 'https://ibps.in/careers',
                'domain' => 'ibps.in',
                'crawl_method' => CrawlMethod::HttpStatic,
                'crawl_frequency_minutes' => 180,
                'trust_level' => TrustLevel::OfficialVerified,
                'configuration' => [
                    'selector' => '.career-item',
                ],
            ],
        ];

        foreach ($sources as $data) {
            $institution = Institution::where('slug', $data['institution_slug'])->first();

            if (! $institution) {
                continue;
            }

            Source::firstOrCreate(
                [
                    'institution_id' => $institution->id,
                    'url' => $data['url'],
                ],
                [
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'canonical_url' => $data['canonical_url'],
                    'domain' => $data['domain'],
                    'crawl_method' => $data['crawl_method'],
                    'crawl_frequency_minutes' => $data['crawl_frequency_minutes'],
                    'status' => 'active',
                    'trust_level' => $data['trust_level'],
                    'next_crawl_at' => now(),
                    'configuration' => $data['configuration'],
                    'metadata' => [],
                ]
            );
        }
    }
}
