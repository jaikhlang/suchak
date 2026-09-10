<?php

namespace Database\Seeders;

use App\Actions\Corrigendum\ApplyCorrigendumPatchAction;
use App\Actions\Publishing\GeneratePublicPostAction;
use App\Enums\EmploymentType;
use App\Enums\NoticeStatus;
use App\Enums\NoticeType;
use App\Enums\QuotaType;
use App\Enums\ReservationCategory;
use App\Models\ApplicationDetail;
use App\Models\ArtifactExtraction;
use App\Models\CrawlRun;
use App\Models\Evidence;
use App\Models\Institution;
use App\Models\Notice;
use App\Models\Position;
use App\Models\PositionReservation;
use App\Models\Source;
use App\Models\SourceArtifact;
use Illuminate\Database\Seeder;

class NoticeSeeder extends Seeder
{
    public function run(): void
    {
        $upsc = Institution::where('slug', 'upsc')->first();
        $ssc = Institution::where('slug', 'ssc')->first();
        $rrb = Institution::where('slug', 'rrb')->first();
        $uppsc = Institution::where('slug', 'uppsc')->first();
        $bpsc = Institution::where('slug', 'bpsc')->first();

        $postGenerator = app(GeneratePublicPostAction::class);
        $corrigendumPatcher = app(ApplyCorrigendumPatchAction::class);

        // =========================================================================
        // 1. UPSC Engineering Services Examination 2026 (Published)
        // =========================================================================
        $notice1 = Notice::where('slug', 'upsc-engineering-services-examination-ese-2026')->first();
        if (! $notice1 && $upsc) {
            $sourceUpsc = Source::firstOrCreate(
                ['institution_id' => $upsc->id, 'url' => 'https://upsc.gov.in/examinations/active-examinations'],
                [
                    'name' => 'UPSC Active Examinations Noticeboard',
                    'type' => 'pdf_noticeboard',
                    'domain' => 'upsc.gov.in',
                    'crawl_method' => 'http_static',
                    'status' => 'active',
                    'trust_level' => 'official_verified',
                ]
            );

            $crawlRunUpsc = CrawlRun::create([
                'source_id' => $sourceUpsc->id,
                'status' => 'completed',
                'started_at' => now()->subHours(2),
                'finished_at' => now()->subHours(1)->subMinutes(50),
                'items_discovered' => 1,
                'items_fetched' => 1,
                'http_status' => 200,
            ]);

            $docText1 = <<<'TEXT'
UNION PUBLIC SERVICE COMMISSION
EXAMINATION NOTICE NO. 04/2026-ENG
ENGINEERING SERVICES EXAMINATION, 2026
(LAST DATE FOR SUBMISSION OF APPLICATIONS : 15/10/2026)

The Union Public Service Commission will hold the Engineering Services Examination, 2026 from 15th February, 2027.
The candidates applying for the examination should ensure that they fulfill all eligibility conditions.

1. CANDIDATES TO ENSURE THEIR ELIGIBILITY FOR THE EXAMINATION:
All candidates must apply online through the official portal https://upsconline.nic.in.

2. VACANCIES:
The number of vacancies to be filled on the results of the examination is expected to be approximately 102 including reservations.
Category I: Civil Engineering - 45 Posts
Category II: Mechanical Engineering - 30 Posts
Category III: Electrical Engineering - 27 Posts

Reservations will be made for candidates belonging to Scheduled Castes (15%), Scheduled Tribes (7.5%), Other Backward Classes (27%), and Economically Weaker Sections (10%).

3. AGE LIMITS:
A candidate for this examination must have attained the age of 21 years and must not have attained the age of 30 years on the 1st January, 2027.
The upper age-limit of 30 years will be relaxable up to 35 years in the case of Government servants.
Relaxation of upper age limit for SC/ST is 5 years, for OBC is 3 years.

4. FEE:
Candidates (excepting Female/SC/ST/PwBD candidates who are exempted from payment of fee) are required to pay a fee of Rs. 200/- (Rupees Two Hundred only).
Online payment may be made using net banking or debit/credit cards.

Closing Date for submission of online applications: 15/10/2026 till 18:00 Hours.
TEXT;

            $artifact1 = SourceArtifact::create([
                'source_id' => $sourceUpsc->id,
                'crawl_run_id' => $crawlRunUpsc->id,
                'type' => 'pdf',
                'url' => 'https://upsc.gov.in/sites/default/files/Notice-ESE-2026-ENG.pdf',
                'canonical_url' => 'https://upsc.gov.in/examinations/ese-2026',
                'content_hash' => hash('sha256', $docText1),
                'mime_type' => 'application/pdf',
                'storage_disk' => 'local',
                'storage_path' => 'artifacts/sources/upsc-ese-2026.pdf',
                'file_size_bytes' => 1048576,
                'retrieved_at' => now()->subHours(2),
            ]);

            $extraction1 = ArtifactExtraction::create([
                'artifact_id' => $artifact1->id,
                'method' => 'docling_table',
                'status' => 'completed',
                'clean_text' => $docText1,
                'raw_text' => $docText1,
                'page_count' => 38,
                'confidence_score' => 0.9850,
                'processor_name' => 'docling-paddleocr-v3',
                'started_at' => now()->subHours(2),
                'finished_at' => now()->subHours(1)->subMinutes(58),
            ]);

            $notice1 = Notice::create([
                'institution_id' => $upsc->id,
                'source_id' => $sourceUpsc->id,
                'source_artifact_id' => $artifact1->id,
                'notice_type' => NoticeType::Recruitment,
                'title' => 'UPSC Engineering Services Examination (ESE) 2026',
                'slug' => 'upsc-engineering-services-examination-ese-2026',
                'reference_number' => '04/2026-ENG',
                'summary' => 'Recruitment of 102 Assistant Executive Engineers across Civil, Mechanical, and Electrical disciplines in Central Government Engineering Services.',
                'status' => NoticeStatus::Published,
                'confidence_score' => 0.9850,
                'is_corrigendum' => false,
                'published_at' => '2026-09-01',
                'application_start_at' => '2026-09-01 10:00:00',
                'application_end_at' => '2026-10-15 18:00:00',
                'fee_payment_end_at' => '2026-10-15 18:00:00',
                'tentative_exam_date_text' => '15th February 2027',
                'exam_start_at' => '2027-02-15 09:00:00',
                'canonical_source_url' => 'https://upsc.gov.in/examinations/ese-2026',
                'total_vacancies' => 102,
                'first_seen_at' => now()->subHours(2),
                'last_seen_at' => now()->subHours(2),
                'metadata' => [
                    'source_trust' => 'official_verified',
                    'hallucination_warning' => false,
                ],
            ]);

            $civilPos = Position::create([
                'notice_id' => $notice1->id,
                'title' => 'Civil Engineering Cadre (Group A)',
                'post_code' => 'CE-01',
                'department' => 'Central Engineering Service',
                'total_vacancies' => 45,
                'employment_type' => EmploymentType::Permanent,
                'pay_level' => 'Level 10 (7th CPC)',
                'pay_scale_text' => '₹56,100 - ₹1,77,500',
            ]);

            PositionReservation::create([
                'position_id' => $civilPos->id,
                'category' => ReservationCategory::UR,
                'quota_type' => QuotaType::Vertical,
                'vacancies' => 18,
            ]);
            PositionReservation::create([
                'position_id' => $civilPos->id,
                'category' => ReservationCategory::OBC_NCL,
                'quota_type' => QuotaType::Vertical,
                'vacancies' => 12,
            ]);
            PositionReservation::create([
                'position_id' => $civilPos->id,
                'category' => ReservationCategory::SC,
                'quota_type' => QuotaType::Vertical,
                'vacancies' => 7,
            ]);
            PositionReservation::create([
                'position_id' => $civilPos->id,
                'category' => ReservationCategory::ST,
                'quota_type' => QuotaType::Vertical,
                'vacancies' => 4,
            ]);
            PositionReservation::create([
                'position_id' => $civilPos->id,
                'category' => ReservationCategory::EWS,
                'quota_type' => QuotaType::Vertical,
                'vacancies' => 4,
            ]);

            ApplicationDetail::create([
                'notice_id' => $notice1->id,
                'application_mode' => 'online',
                'apply_url' => 'https://upsconline.nic.in',
                'official_notification_pdf_url' => 'https://upsc.gov.in/sites/default/files/Notice-ESE-2026-ENG.pdf',
                'general_fee' => 200,
                'is_exempted_for_sc_st' => true,
                'is_exempted_for_female' => true,
                'is_exempted_for_pwbd' => true,
            ]);

            Evidence::create([
                'artifact_id' => $artifact1->id,
                'extraction_id' => $extraction1->id,
                'notice_id' => $notice1->id,
                'field_name' => 'application_end_at',
                'extracted_value' => '15/10/2026',
                'page_number' => 1,
                'verbatim_text_fragment' => 'LAST DATE FOR SUBMISSION OF APPLICATIONS : 15/10/2026',
                'confidence_score' => 0.99,
                'is_verified_by_human' => true,
            ]);

            $postGenerator->execute($notice1);
        }

        // =========================================================================
        // 2. SSC Combined Graduate Level (CGL) 2026 (Pending Review / Warning)
        // =========================================================================
        $notice2 = Notice::where('slug', 'ssc-combined-graduate-level-examination-ssc-cgl-2026')->first();
        if (! $notice2 && $ssc) {
            $sourceSsc = Source::firstOrCreate(
                ['institution_id' => $ssc->id, 'url' => 'https://ssc.gov.in/notices/latest'],
                [
                    'name' => 'SSC Latest Notifications',
                    'type' => 'pdf_noticeboard',
                    'domain' => 'ssc.gov.in',
                    'crawl_method' => 'http_static',
                    'status' => 'active',
                    'trust_level' => 'official_verified',
                ]
            );

            $crawlRunSsc = CrawlRun::create([
                'source_id' => $sourceSsc->id,
                'status' => 'completed',
                'started_at' => now()->subHours(1),
                'finished_at' => now()->subMinutes(45),
                'items_discovered' => 1,
                'items_fetched' => 1,
                'http_status' => 200,
            ]);

            $docText2 = <<<'TEXT'
STAFF SELECTION COMMISSION
NOTICE
COMBINED GRADUATE LEVEL EXAMINATION, 2026
Dates for submission of online applications: 24-09-2026 to 24-10-2026
Last date and time for receipt of online applications: 24-10-2026 (23:00)
Schedule of Tier-I (Computer Based Examination): Dec, 2026

The Staff Selection Commission will hold Combined Graduate Level Examination, 2026 for filling up of various Group 'B' and Group 'C' posts in different Ministries/ Departments/ Organizations of Government of India.
Vacancies: There are approx. 17727 vacancies.
Age limit: 18-30 years.
TEXT;

            $artifact2 = SourceArtifact::create([
                'source_id' => $sourceSsc->id,
                'crawl_run_id' => $crawlRunSsc->id,
                'type' => 'pdf',
                'url' => 'https://ssc.gov.in/notices/CGL_2026_Notice.pdf',
                'canonical_url' => 'https://ssc.gov.in/portal/cgl-2026',
                'content_hash' => hash('sha256', $docText2),
                'mime_type' => 'application/pdf',
                'storage_disk' => 'local',
                'storage_path' => 'artifacts/sources/ssc-cgl-2026.pdf',
                'file_size_bytes' => 2097152,
                'retrieved_at' => now()->subHours(1),
            ]);

            $extraction2 = ArtifactExtraction::create([
                'artifact_id' => $artifact2->id,
                'method' => 'docling_table',
                'status' => 'completed',
                'clean_text' => $docText2,
                'raw_text' => $docText2,
                'page_count' => 65,
                'confidence_score' => 0.7200,
                'processor_name' => 'docling-paddleocr-v3',
                'started_at' => now()->subMinutes(50),
                'finished_at' => now()->subMinutes(46),
            ]);

            $notice2 = Notice::create([
                'institution_id' => $ssc->id,
                'source_id' => $sourceSsc->id,
                'source_artifact_id' => $artifact2->id,
                'notice_type' => NoticeType::Recruitment,
                'title' => 'Combined Graduate Level Examination (SSC CGL) 2026',
                'slug' => 'ssc-combined-graduate-level-examination-ssc-cgl-2026',
                'reference_number' => 'HQ-PPI03/11/2026-PP_1',
                'summary' => 'Group B and C recruitment across Ministries and Central Government departments.',
                'status' => NoticeStatus::PendingReview,
                'confidence_score' => 0.6800,
                'is_corrigendum' => false,
                'published_at' => now()->subDays(1),
                'application_start_at' => '2026-09-24',
                'application_end_at' => '2026-10-24',
                'canonical_source_url' => 'https://ssc.gov.in/notices/CGL_2026_Notice.pdf',
                'total_vacancies' => 17727,
                'first_seen_at' => now(),
                'last_seen_at' => now(),
                'metadata' => [
                    'hallucination_warning' => true,
                    'grounding_failures' => [
                        [
                            'field' => 'age_relaxation',
                            'claimed_value' => 'OBC: 5 Years',
                            'missing_snippet' => 'OBC candidates receive 5 years relaxation',
                        ],
                    ],
                ],
            ]);

            Position::create([
                'notice_id' => $notice2->id,
                'title' => 'Assistant Section Officer (CSS)',
                'post_code' => 'B01',
                'department' => 'Central Secretariat Service',
                'total_vacancies' => 950,
                'employment_type' => EmploymentType::Permanent,
                'pay_level' => 'Level 7 (7th CPC)',
            ]);

            ApplicationDetail::create([
                'notice_id' => $notice2->id,
                'application_mode' => 'online',
                'apply_url' => 'https://ssc.gov.in',
                'official_notification_pdf_url' => 'https://ssc.gov.in/notices/CGL_2026_Notice.pdf',
                'general_fee' => 100,
                'is_exempted_for_sc_st' => true,
                'is_exempted_for_female' => true,
            ]);

            Evidence::create([
                'artifact_id' => $artifact2->id,
                'extraction_id' => $extraction2->id,
                'notice_id' => $notice2->id,
                'field_name' => 'application_end_at',
                'extracted_value' => '24-10-2026',
                'page_number' => 1,
                'verbatim_text_fragment' => 'Last date and time for receipt of online applications: 24-10-2026 (23:00)',
                'confidence_score' => 0.95,
                'is_verified_by_human' => true,
            ]);
        }

        // =========================================================================
        // 3. RRB CEN 05/2026 NTPC (Railway Recruitment Boards - 11,558 Posts)
        // =========================================================================
        $notice3 = Notice::where('slug', 'rrb-cen-05-2026-ntpc-recruitment')->first();
        if (! $notice3 && $rrb) {
            $sourceRrb = Source::firstOrCreate(
                ['institution_id' => $rrb->id, 'url' => 'https://indianrailways.gov.in/railwayboard/view_section.jsp?lang=0&id=0,4,1244'],
                [
                    'name' => 'RRB Centralised Employment Notices',
                    'type' => 'pdf_noticeboard',
                    'domain' => 'indianrailways.gov.in',
                    'crawl_method' => 'http_static',
                    'status' => 'active',
                    'trust_level' => 'official_verified',
                ]
            );

            $docText3 = <<<'TEXT'
GOVERNMENT OF INDIA, MINISTRY OF RAILWAYS
RAILWAY RECRUITMENT BOARDS
CENTRALISED EMPLOYMENT NOTICE (CEN) No. 05/2026
RECRUITMENT FOR NON-TECHNICAL POPULAR CATEGORIES (NTPC)

Applications are invited from eligible candidates for the following graduate and undergraduate posts:
Opening date of online registration: 14/09/2026
Closing date of online registration: 13/10/2026 (23:59 hrs)

TOTAL VACANCIES: 11,558 Posts across 21 Railway Recruitment Boards.

1. Commercial Apprentice (Level 6): 2,450 Posts (Pay Scale: ₹35,400 - ₹1,12,400)
2. Station Master (Level 6): 4,800 Posts (Pay Scale: ₹35,400 - ₹1,12,400)
3. Goods Train Manager (Level 5): 4,308 Posts (Pay Scale: ₹29,200 - ₹92,300)

Age limit (as on 01/01/2027): 18 to 33 years for graduate posts.
Application Fee: ₹500 for Unreserved/OBC (₹400 refunded on attending CBT-1).
Fee for SC/ST/Ex-Servicemen/PwBD/Female: ₹250 (Fully refunded on attending CBT-1).
TEXT;

            $crawlRunRrb = CrawlRun::create([
                'source_id' => $sourceRrb->id,
                'status' => 'completed',
                'started_at' => now()->subHours(3),
                'finished_at' => now()->subHours(2)->subMinutes(50),
                'items_discovered' => 1,
                'items_fetched' => 1,
                'http_status' => 200,
            ]);

            $artifact3 = SourceArtifact::create([
                'source_id' => $sourceRrb->id,
                'crawl_run_id' => $crawlRunRrb->id,
                'type' => 'pdf',
                'url' => 'https://indianrailways.gov.in/cen-05-2026-ntpc.pdf',
                'canonical_url' => 'https://rrbapply.gov.in',
                'content_hash' => hash('sha256', $docText3),
                'mime_type' => 'application/pdf',
                'storage_disk' => 'local',
                'storage_path' => 'artifacts/sources/rrb-ntpc-2026.pdf',
                'file_size_bytes' => 3145728,
                'retrieved_at' => now()->subHours(3),
            ]);

            $extraction3 = ArtifactExtraction::create([
                'artifact_id' => $artifact3->id,
                'method' => 'docling_table',
                'status' => 'completed',
                'clean_text' => $docText3,
                'raw_text' => $docText3,
                'page_count' => 72,
                'confidence_score' => 0.9700,
                'processor_name' => 'docling-paddleocr-v3',
                'started_at' => now()->subHours(3),
                'finished_at' => now()->subHours(2)->subMinutes(55),
            ]);

            $notice3 = Notice::create([
                'institution_id' => $rrb->id,
                'source_id' => $sourceRrb->id,
                'source_artifact_id' => $artifact3->id,
                'notice_type' => NoticeType::Recruitment,
                'title' => 'RRB NTPC Graduate & Undergraduate Recruitment (CEN 05/2026)',
                'slug' => 'rrb-cen-05-2026-ntpc-recruitment',
                'reference_number' => 'CEN 05/2026',
                'summary' => 'Ministry of Railways mega recruitment drive for 11,558 vacancies in Station Master, Commercial Apprentice, and Goods Train Manager roles.',
                'status' => NoticeStatus::Published,
                'confidence_score' => 0.9700,
                'is_corrigendum' => false,
                'published_at' => '2026-09-14',
                'application_start_at' => '2026-09-14 00:00:00',
                'application_end_at' => '2026-10-13 23:59:00',
                'fee_payment_end_at' => '2026-10-14 23:59:00',
                'tentative_exam_date_text' => 'December 2026 to January 2027',
                'canonical_source_url' => 'https://rrbapply.gov.in',
                'total_vacancies' => 11558,
                'first_seen_at' => now()->subHours(3),
                'last_seen_at' => now()->subHours(3),
                'metadata' => ['source_trust' => 'official_verified'],
            ]);

            $posSm = Position::create([
                'notice_id' => $notice3->id,
                'title' => 'Station Master',
                'post_code' => 'NTPC-SM-02',
                'department' => 'Operating Department',
                'total_vacancies' => 4800,
                'employment_type' => EmploymentType::Permanent,
                'pay_level' => 'Level 6 (7th CPC)',
                'pay_scale_text' => '₹35,400 - ₹1,12,400',
            ]);

            PositionReservation::create([
                'position_id' => $posSm->id,
                'category' => ReservationCategory::UR,
                'quota_type' => QuotaType::Vertical,
                'vacancies' => 1920,
            ]);
            PositionReservation::create([
                'position_id' => $posSm->id,
                'category' => ReservationCategory::OBC_NCL,
                'quota_type' => QuotaType::Vertical,
                'vacancies' => 1296,
            ]);
            PositionReservation::create([
                'position_id' => $posSm->id,
                'category' => ReservationCategory::SC,
                'quota_type' => QuotaType::Vertical,
                'vacancies' => 720,
            ]);
            PositionReservation::create([
                'position_id' => $posSm->id,
                'category' => ReservationCategory::ST,
                'quota_type' => QuotaType::Vertical,
                'vacancies' => 360,
            ]);
            PositionReservation::create([
                'position_id' => $posSm->id,
                'category' => ReservationCategory::EWS,
                'quota_type' => QuotaType::Vertical,
                'vacancies' => 480,
            ]);

            Position::create([
                'notice_id' => $notice3->id,
                'title' => 'Commercial Apprentice',
                'post_code' => 'NTPC-CA-01',
                'department' => 'Commercial Department',
                'total_vacancies' => 2450,
                'employment_type' => EmploymentType::Permanent,
                'pay_level' => 'Level 6 (7th CPC)',
                'pay_scale_text' => '₹35,400 - ₹1,12,400',
            ]);

            Position::create([
                'notice_id' => $notice3->id,
                'title' => 'Goods Train Manager',
                'post_code' => 'NTPC-GTM-03',
                'department' => 'Operating Department',
                'total_vacancies' => 4308,
                'employment_type' => EmploymentType::Permanent,
                'pay_level' => 'Level 5 (7th CPC)',
                'pay_scale_text' => '₹29,200 - ₹92,300',
            ]);

            ApplicationDetail::create([
                'notice_id' => $notice3->id,
                'application_mode' => 'online',
                'apply_url' => 'https://rrbapply.gov.in',
                'general_fee' => 500,
                'is_exempted_for_sc_st' => false,
                'is_exempted_for_female' => false,
            ]);

            Evidence::create([
                'artifact_id' => $artifact3->id,
                'extraction_id' => $extraction3->id,
                'notice_id' => $notice3->id,
                'field_name' => 'application_end_at',
                'extracted_value' => '13/10/2026',
                'page_number' => 1,
                'verbatim_text_fragment' => 'Closing date of online registration: 13/10/2026 (23:59 hrs)',
                'confidence_score' => 0.99,
                'is_verified_by_human' => true,
            ]);

            $postGenerator->execute($notice3);
        }

        // =========================================================================
        // 4. UPPSC Combined State / Upper Subordinate Services (PCS) 2026 (220 Posts)
        // =========================================================================
        $notice4 = Notice::where('slug', 'uppsc-combined-state-upper-subordinate-services-pcs-2026')->first();
        if (! $notice4 && $uppsc) {
            $sourceUppsc = Source::firstOrCreate(
                ['institution_id' => $uppsc->id, 'url' => 'https://uppsc.up.nic.in/AllNotifications.aspx'],
                [
                    'name' => 'UPPSC Online Notifications',
                    'type' => 'recruitment_portal',
                    'domain' => 'uppsc.up.nic.in',
                    'crawl_method' => 'http_static',
                    'status' => 'active',
                    'trust_level' => 'official_verified',
                ]
            );

            $docText4 = <<<'TEXT'
UTTAR PRADESH PUBLIC SERVICE COMMISSION, PRAYAGRAJ
ADVT. NO. : A-2/E-1/2026
COMBINED STATE / UPPER SUBORDINATE SERVICES (PCS) EXAMINATION - 2026
Date of Commencement of On-line Application: 01/01/2026
Last Date for Receipt of Examination Fees in the Bank: 29/01/2026
Last Date for Submission of On-line Application: 02/02/2026

Presently the number of vacancies for the Combined State / Upper Subordinate Services Examination is about 220.
Pay Scale: ₹9,300-34,800 Grade Pay ₹4,600 to ₹15,600-39,100 Grade Pay ₹5,400 (Level-10 Pay Matrix ₹56,100-₹1,77,500).

Posts include:
1. Deputy Collector (Executive Branch) - 45 Posts
2. Deputy Superintendent of Police (DSP) - 35 Posts
3. Block Development Officer (BDO) - 60 Posts
4. Assistant Regional Transport Officer (ARTO) - 20 Posts
5. Sub-Registrar / Commercial Tax Officer - 60 Posts
TEXT;

            $crawlRunUppsc = CrawlRun::create([
                'source_id' => $sourceUppsc->id,
                'status' => 'completed',
                'started_at' => now()->subDays(2),
                'finished_at' => now()->subDays(2)->addMinutes(10),
                'items_discovered' => 1,
                'items_fetched' => 1,
                'http_status' => 200,
            ]);

            $artifact4 = SourceArtifact::create([
                'source_id' => $sourceUppsc->id,
                'crawl_run_id' => $crawlRunUppsc->id,
                'type' => 'pdf',
                'url' => 'https://uppsc.up.nic.in/pcs-2026.pdf',
                'canonical_url' => 'https://uppsc.up.nic.in',
                'content_hash' => hash('sha256', $docText4),
                'mime_type' => 'application/pdf',
                'storage_disk' => 'local',
                'storage_path' => 'artifacts/sources/uppsc-pcs-2026.pdf',
                'file_size_bytes' => 1572864,
                'retrieved_at' => now()->subDays(2),
            ]);

            $extraction4 = ArtifactExtraction::create([
                'artifact_id' => $artifact4->id,
                'method' => 'docling_table',
                'status' => 'completed',
                'clean_text' => $docText4,
                'raw_text' => $docText4,
                'page_count' => 28,
                'confidence_score' => 0.9800,
                'processor_name' => 'docling-paddleocr-v3',
                'started_at' => now()->subDays(2),
                'finished_at' => now()->subDays(2),
            ]);

            $notice4 = Notice::create([
                'institution_id' => $uppsc->id,
                'source_id' => $sourceUppsc->id,
                'source_artifact_id' => $artifact4->id,
                'notice_type' => NoticeType::Recruitment,
                'title' => 'UPPSC Combined State / Upper Subordinate Services (PCS) Examination 2026',
                'slug' => 'uppsc-combined-state-upper-subordinate-services-pcs-2026',
                'reference_number' => 'A-2/E-1/2026',
                'summary' => 'Uttar Pradesh State Civil Services recruitment for 220 gazetted executive posts including Deputy Collector (SDM) and DSP.',
                'status' => NoticeStatus::Published,
                'confidence_score' => 0.9800,
                'is_corrigendum' => false,
                'published_at' => '2026-01-01',
                'application_start_at' => '2026-01-01 10:00:00',
                'application_end_at' => '2026-11-20 23:59:00',
                'tentative_exam_date_text' => 'November 2026',
                'canonical_source_url' => 'https://uppsc.up.nic.in',
                'total_vacancies' => 220,
                'first_seen_at' => now()->subDays(2),
                'last_seen_at' => now()->subDays(2),
                'metadata' => ['source_trust' => 'official_verified'],
            ]);

            Position::create([
                'notice_id' => $notice4->id,
                'title' => 'Deputy Collector / Sub-Divisional Magistrate (SDM)',
                'post_code' => 'PCS-DC',
                'department' => 'Appointments and Personnel Department',
                'total_vacancies' => 45,
                'employment_type' => EmploymentType::Permanent,
                'pay_level' => 'Level 10 (7th CPC)',
                'pay_scale_text' => '₹56,100 - ₹1,77,500',
            ]);

            Position::create([
                'notice_id' => $notice4->id,
                'title' => 'Deputy Superintendent of Police (DSP)',
                'post_code' => 'PCS-DSP',
                'department' => 'Home (Police) Department',
                'total_vacancies' => 35,
                'employment_type' => EmploymentType::Permanent,
                'pay_level' => 'Level 10 (7th CPC)',
                'pay_scale_text' => '₹56,100 - ₹1,77,500',
            ]);

            ApplicationDetail::create([
                'notice_id' => $notice4->id,
                'application_mode' => 'online',
                'apply_url' => 'https://uppsc.up.nic.in',
                'general_fee' => 125,
                'is_exempted_for_sc_st' => false,
                'is_exempted_for_female' => false,
            ]);

            Evidence::create([
                'artifact_id' => $artifact4->id,
                'extraction_id' => $extraction4->id,
                'notice_id' => $notice4->id,
                'field_name' => 'total_vacancies',
                'extracted_value' => '220',
                'page_number' => 1,
                'verbatim_text_fragment' => 'Combined State / Upper Subordinate Services Examination is about 220',
                'confidence_score' => 0.98,
                'is_verified_by_human' => true,
            ]);

            $postGenerator->execute($notice4);
        }

        // =========================================================================
        // 5. BPSC 71st Integrated Combined Competitive Examination 2026 (1,950 Posts)
        // =========================================================================
        $notice5 = Notice::where('slug', 'bpsc-71st-integrated-combined-competitive-examination-2026')->first();
        if (! $notice5 && $bpsc) {
            $sourceBpsc = Source::firstOrCreate(
                ['institution_id' => $bpsc->id, 'url' => 'https://bpsc.bih.nic.in/Notice.htm'],
                [
                    'name' => 'BPSC Recruitment Notices',
                    'type' => 'recruitment_portal',
                    'domain' => 'bpsc.bih.nic.in',
                    'crawl_method' => 'http_static',
                    'status' => 'active',
                    'trust_level' => 'official_verified',
                ]
            );

            $docText5 = <<<'TEXT'
BIHAR PUBLIC SERVICE COMMISSION
15, NEHRU PATH (BELEY ROAD), PATNA - 800001
IMPORTANT NOTICE: 71st INTEGRATED COMBINED (PRELIMINARY) COMPETITIVE EXAMINATION
ADVERTISEMENT NO. 35/2026

Online applications are invited from eligible Indian citizens for appointment to 1,950 vacant posts across Bihar Administrative Service, Bihar Police Service, and various state departments.
Registration window: 28/09/2026 to 28/10/2026.
Application fee: ₹600 for General/OBC; ₹150 for SC/ST and Female candidates of Bihar.
TEXT;

            $crawlRunBpsc = CrawlRun::create([
                'source_id' => $sourceBpsc->id,
                'status' => 'completed',
                'started_at' => now()->subDays(1),
                'finished_at' => now()->subDays(1)->addMinutes(15),
                'items_discovered' => 1,
                'items_fetched' => 1,
                'http_status' => 200,
            ]);

            $artifact5 = SourceArtifact::create([
                'source_id' => $sourceBpsc->id,
                'crawl_run_id' => $crawlRunBpsc->id,
                'type' => 'pdf',
                'url' => 'https://bpsc.bih.nic.in/Advt-35-2026.pdf',
                'canonical_url' => 'https://onlinebpsc.bihar.gov.in',
                'content_hash' => hash('sha256', $docText5),
                'mime_type' => 'application/pdf',
                'storage_disk' => 'local',
                'storage_path' => 'artifacts/sources/bpsc-cce-2026.pdf',
                'file_size_bytes' => 1258291,
                'retrieved_at' => now()->subDays(1),
            ]);

            $extraction5 = ArtifactExtraction::create([
                'artifact_id' => $artifact5->id,
                'method' => 'docling_table',
                'status' => 'completed',
                'clean_text' => $docText5,
                'raw_text' => $docText5,
                'page_count' => 18,
                'confidence_score' => 0.9650,
                'processor_name' => 'docling-paddleocr-v3',
                'started_at' => now()->subDays(1),
                'finished_at' => now()->subDays(1),
            ]);

            $notice5 = Notice::create([
                'institution_id' => $bpsc->id,
                'source_id' => $sourceBpsc->id,
                'source_artifact_id' => $artifact5->id,
                'notice_type' => NoticeType::Recruitment,
                'title' => 'BPSC 71st Integrated Combined Competitive Examination 2026',
                'slug' => 'bpsc-71st-integrated-combined-competitive-examination-2026',
                'reference_number' => '35/2026',
                'summary' => 'Bihar Public Service Commission 71st CCE for 1,950 administrative and civil executive posts across Bihar State Government.',
                'status' => NoticeStatus::Published,
                'confidence_score' => 0.9650,
                'is_corrigendum' => false,
                'published_at' => '2026-09-28',
                'application_start_at' => '2026-09-28 09:00:00',
                'application_end_at' => '2026-10-28 23:59:00',
                'tentative_exam_date_text' => '13th December 2026',
                'canonical_source_url' => 'https://onlinebpsc.bihar.gov.in',
                'total_vacancies' => 1950,
                'first_seen_at' => now()->subDays(1),
                'last_seen_at' => now()->subDays(1),
                'metadata' => ['source_trust' => 'official_verified'],
            ]);

            Position::create([
                'notice_id' => $notice5->id,
                'title' => 'Bihar Administrative Service (Sub-Divisional Officer / Senior Deputy Collector)',
                'post_code' => 'BAS-01',
                'department' => 'General Administration Department, Bihar',
                'total_vacancies' => 200,
                'employment_type' => EmploymentType::Permanent,
                'pay_level' => 'Level 9 (7th CPC)',
                'pay_scale_text' => '₹53,100 - ₹1,67,800',
            ]);

            Position::create([
                'notice_id' => $notice5->id,
                'title' => 'Bihar Police Service (DSP)',
                'post_code' => 'BPS-02',
                'department' => 'Home (Police) Department, Bihar',
                'total_vacancies' => 120,
                'employment_type' => EmploymentType::Permanent,
                'pay_level' => 'Level 9 (7th CPC)',
                'pay_scale_text' => '₹53,100 - ₹1,67,800',
            ]);

            ApplicationDetail::create([
                'notice_id' => $notice5->id,
                'application_mode' => 'online',
                'apply_url' => 'https://onlinebpsc.bihar.gov.in',
                'general_fee' => 600,
                'is_exempted_for_sc_st' => false,
                'is_exempted_for_female' => false,
            ]);

            Evidence::create([
                'artifact_id' => $artifact5->id,
                'extraction_id' => $extraction5->id,
                'notice_id' => $notice5->id,
                'field_name' => 'total_vacancies',
                'extracted_value' => '1,950',
                'page_number' => 1,
                'verbatim_text_fragment' => 'appointment to 1,950 vacant posts across Bihar Administrative Service',
                'confidence_score' => 0.98,
                'is_verified_by_human' => true,
            ]);

            $postGenerator->execute($notice5);
        }

        // =========================================================================
        // 6. UPSC ESE 2026 Corrigendum-1 (शुद्धिपत्र - Last Date Extension & Vacancies)
        // =========================================================================
        if ($notice1 && ! $notice1->corrigenda()->exists()) {
            $docTextCorr = <<<'TEXT'
UNION PUBLIC SERVICE COMMISSION
CORRIGENDUM / शुद्धिपत्र
EXAMINATION NOTICE NO. 04/2026-ENG (CORR-1)
ENGINEERING SERVICES EXAMINATION, 2026

Reference is invited to Union Public Service Commission Examination Notice No. 04/2026-ENG published on 01/09/2026 for Engineering Services Examination 2026.
It is notified for the information of all concerned that:
1. The last date for submission of online applications on https://upsconline.nic.in is hereby EXTENDED from 15/10/2026 to 25/10/2026 (18:00 Hours).
2. The tentative vacancies are revised upward from 102 to 125 posts due to additional indent received from the Ministry of Railways.
TEXT;

            $sourceUpsc = $notice1->source;
            $crawlRunCorr = CrawlRun::create([
                'source_id' => $sourceUpsc->id,
                'status' => 'completed',
                'started_at' => now()->subMinutes(35),
                'finished_at' => now()->subMinutes(30),
                'items_discovered' => 1,
                'items_fetched' => 1,
                'http_status' => 200,
            ]);

            $artifactCorr = SourceArtifact::create([
                'source_id' => $sourceUpsc->id,
                'crawl_run_id' => $crawlRunCorr->id,
                'type' => 'pdf',
                'url' => 'https://upsc.gov.in/sites/default/files/Corrigendum-1-ESE-2026.pdf',
                'canonical_url' => 'https://upsc.gov.in/examinations/ese-2026/corrigendum-1',
                'content_hash' => hash('sha256', $docTextCorr),
                'mime_type' => 'application/pdf',
                'storage_disk' => 'local',
                'storage_path' => 'artifacts/sources/upsc-ese-2026-corr-1.pdf',
                'file_size_bytes' => 524288,
                'retrieved_at' => now()->subMinutes(30),
            ]);

            $extractionCorr = ArtifactExtraction::create([
                'artifact_id' => $artifactCorr->id,
                'method' => 'docling_table',
                'status' => 'completed',
                'clean_text' => $docTextCorr,
                'raw_text' => $docTextCorr,
                'page_count' => 2,
                'confidence_score' => 0.9900,
                'processor_name' => 'docling-paddleocr-v3',
                'started_at' => now()->subMinutes(30),
                'finished_at' => now()->subMinutes(29),
            ]);

            $childCorrigendum = Notice::create([
                'institution_id' => $upsc->id,
                'source_id' => $sourceUpsc->id,
                'source_artifact_id' => $artifactCorr->id,
                'notice_type' => NoticeType::Corrigendum,
                'title' => 'Corrigendum-I: UPSC Engineering Services Examination 2026 - Deadline Extension & Vacancy Increase',
                'slug' => 'upsc-corrigendum-1-engineering-services-examination-ese-2026',
                'reference_number' => '04/2026-ENG/CORR-1',
                'summary' => 'Official corrigendum extending last date for submission of online applications to 25/10/2026 and increasing vacancies to 125 posts.',
                'status' => NoticeStatus::Approved,
                'confidence_score' => 0.9900,
                'is_corrigendum' => true,
                'parent_notice_id' => $notice1->id,
                'published_at' => '2026-10-05',
                'application_start_at' => $notice1->application_start_at,
                'application_end_at' => '2026-10-25 18:00:00',
                'fee_payment_end_at' => '2026-10-25 18:00:00',
                'canonical_source_url' => 'https://upsc.gov.in/examinations/ese-2026/corrigendum-1',
                'total_vacancies' => 125,
                'first_seen_at' => now()->subMinutes(30),
                'last_seen_at' => now()->subMinutes(30),
                'metadata' => ['corrigendum_parent_ref' => '04/2026-ENG'],
            ]);

            Evidence::create([
                'artifact_id' => $artifactCorr->id,
                'extraction_id' => $extractionCorr->id,
                'notice_id' => $childCorrigendum->id,
                'field_name' => 'application_end_at',
                'extracted_value' => '25/10/2026',
                'page_number' => 1,
                'verbatim_text_fragment' => 'EXTENDED from 15/10/2026 to 25/10/2026 (18:00 Hours)',
                'confidence_score' => 0.99,
                'is_verified_by_human' => true,
            ]);

            // Apply Corrigendum Patch Action to parent notice
            $corrigendumPatcher->execute(
                parentNotice: $notice1,
                childCorrigendum: $childCorrigendum,
                patchFields: [
                    'application_end_at' => '2026-10-25 18:00:00',
                    'fee_payment_end_at' => '2026-10-25 18:00:00',
                    'total_vacancies' => 125,
                ],
                actor: null,
                reason: 'Corrigendum-I: Last date extended to 25/10/2026 and vacancies increased from 102 to 125.'
            );
        }

        // Ensure all published notices have editorial posts
        foreach (Notice::where('status', NoticeStatus::Published)->get() as $pubNotice) {
            if (! $pubNotice->post()->exists()) {
                $postGenerator->execute($pubNotice);
            }
        }
    }
}
