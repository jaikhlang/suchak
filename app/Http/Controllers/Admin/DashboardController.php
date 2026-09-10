<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NoticeStatus;
use App\Enums\SourceStatus;
use App\Http\Controllers\Controller;
use App\Models\CandidateSubscription;
use App\Models\CrawlRun;
use App\Models\Institution;
use App\Models\Notice;
use App\Models\Source;
use App\Services\Ingestion\IngestionSidecarClient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected IngestionSidecarClient $sidecarClient
    ) {}

    /**
     * Display the operational intelligence dashboard.
     */
    public function index(Request $request): Response
    {
        // 1. Core KPIs
        $totalVacancies = (int) Notice::whereIn('status', [NoticeStatus::Published, NoticeStatus::Approved])
            ->sum('total_vacancies');

        $publishedCount = Notice::where('status', NoticeStatus::Published)->count();
        $pendingModerationCount = Notice::where('status', NoticeStatus::PendingReview)->count();
        $highRiskCount = Notice::where('status', NoticeStatus::PendingReview)
            ->where(function ($q) {
                $q->where('confidence_score', '<', 0.75)
                    ->orWhereRaw("metadata->>'hallucination_warning' = 'true'");
            })
            ->count();

        $activeSourcesCount = Source::where('status', SourceStatus::Active)->count();
        $failingSourcesCount = Source::where('status', SourceStatus::Failing)
            ->orWhere('consecutive_failures', '>', 0)
            ->count();

        $totalSubscribers = CandidateSubscription::where('is_verified', true)->count();

        // 2. Urgent Moderation Queue (Top 5 requiring review)
        $urgentNotices = Notice::with(['institution:id,name,short_name,slug', 'source:id,name,trust_level'])
            ->where('status', NoticeStatus::PendingReview)
            ->orderBy('confidence_score', 'asc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'institution_id', 'source_id', 'title', 'slug', 'reference_number', 'confidence_score', 'total_vacancies', 'application_end_at', 'is_corrigendum', 'metadata', 'created_at']);

        // 3. Recent Crawl Stream (Top 8 latest runs)
        $recentCrawlRuns = CrawlRun::with(['source:id,name,domain,institution_id', 'source.institution:id,name,short_name'])
            ->latest('started_at')
            ->limit(8)
            ->get(['id', 'source_id', 'status', 'started_at', 'finished_at', 'items_discovered', 'items_fetched', 'items_failed', 'http_status', 'error_code']);

        // 4. Authority Distribution (Top Institutions by Notices)
        $topInstitutions = Institution::withCount([
            'notices',
            'notices as published_notices_count' => fn ($q) => $q->where('status', NoticeStatus::Published),
            'notices as pending_notices_count' => fn ($q) => $q->where('status', NoticeStatus::PendingReview),
        ])
            ->orderByDesc('notices_count')
            ->limit(5)
            ->get(['id', 'name', 'short_name', 'slug', 'institution_type', 'is_verified']);

        // 5. System Health Status
        $sidecarHealthy = $this->sidecarClient->healthCheck();

        return Inertia::render('Dashboard', [
            'metrics' => [
                'total_vacancies' => $totalVacancies,
                'published_notices' => $publishedCount,
                'pending_moderation' => $pendingModerationCount,
                'high_risk_notices' => $highRiskCount,
                'active_sources' => $activeSourcesCount,
                'failing_sources' => $failingSourcesCount,
                'total_subscribers' => $totalSubscribers,
            ],
            'urgent_notices' => $urgentNotices,
            'recent_crawl_runs' => $recentCrawlRuns,
            'top_institutions' => $topInstitutions,
            'system_health' => [
                'sidecar_online' => $sidecarHealthy,
                'database_type' => config('database.default'),
                'queue_driver' => config('queue.default'),
            ],
        ]);
    }
}
