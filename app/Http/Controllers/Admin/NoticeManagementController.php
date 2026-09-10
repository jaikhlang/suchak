<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Publishing\GeneratePublicPostAction;
use App\Enums\NoticeStatus;
use App\Enums\NoticeType;
use App\Http\Controllers\Controller;
use App\Jobs\DispatchN8nWebhookJob;
use App\Jobs\NotifySubscribersOfNoticeJob;
use App\Models\Institution;
use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NoticeManagementController extends Controller
{
    /**
     * Display the notices and corrigenda catalog.
     */
    public function index(Request $request): Response
    {
        $status = $request->input('status');
        $type = $request->input('type');
        $institutionId = $request->input('institution_id');
        $search = $request->input('search');

        $query = Notice::with([
            'institution:id,name,short_name,slug',
            'source:id,name,trust_level',
            'post:id,notice_id,slug,published_at',
        ])->latest('first_seen_at');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($type && $type !== 'all') {
            if ($type === 'corrigendum') {
                $query->where(function ($q) {
                    $q->where('is_corrigendum', true)
                        ->orWhere('notice_type', NoticeType::Corrigendum);
                });
            } else {
                $query->where('notice_type', $type);
            }
        }

        if ($institutionId) {
            $query->where('institution_id', $institutionId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(title) LIKE ?', ['%'.strtolower($search).'%'])
                    ->orWhereRaw('LOWER(reference_number) LIKE ?', ['%'.strtolower($search).'%']);
            });
        }

        $notices = $query->paginate(15)->withQueryString();

        $stats = [
            'total_all' => Notice::count(),
            'published' => Notice::where('status', NoticeStatus::Published)->count(),
            'pending' => Notice::where('status', NoticeStatus::PendingReview)->count(),
            'corrigenda' => Notice::where('is_corrigendum', true)->count(),
        ];

        $institutions = Institution::select('id', 'name', 'short_name')->orderBy('name')->get();

        return Inertia::render('admin/notices/Index', [
            'notices' => $notices,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'institution_id' => $institutionId,
                'search' => $search,
            ],
            'institutions' => $institutions,
        ]);
    }

    /**
     * Display the specified notice in detail with audit trail & corrigendum history.
     */
    public function show(Notice $notice): Response
    {
        $notice->load([
            'institution:id,name,short_name,slug,official_domain,state_id',
            'institution.state:id,name,iso_code',
            'source:id,name,url,domain,trust_level',
            'sourceArtifact:id,url,canonical_url,content_hash,mime_type,retrieved_at',
            'positions.reservations',
            'eligibilityRules',
            'applicationDetail',
            'evidence',
            'post',
            'parentNotice:id,title,slug,reference_number,status',
            'corrigenda:id,title,slug,reference_number,status,published_at',
            'revisions' => fn ($q) => $q->latest('created_at')->limit(10),
            'revisions.creator:id,name',
        ]);

        return Inertia::render('admin/notices/Show', [
            'notice' => $notice,
        ]);
    }

    /**
     * Regenerate public post for the notice.
     */
    public function republish(Notice $notice, GeneratePublicPostAction $postGenerator): RedirectResponse
    {
        $postGenerator->execute($notice);

        return redirect()->back()->with('status', 'Public post regenerated successfully.');
    }

    /**
     * Archive the notice.
     */
    public function archive(Notice $notice): RedirectResponse
    {
        $notice->update(['status' => NoticeStatus::Archived]);

        return redirect()->back()->with('status', 'Notice moved to archived state.');
    }

    /**
     * Trigger outbound distribution (n8n webhook and subscriber alerts).
     */
    public function dispatchDistribution(Notice $notice): RedirectResponse
    {
        DispatchN8nWebhookJob::dispatchSync($notice);
        NotifySubscribersOfNoticeJob::dispatch($notice);

        return redirect()->back()->with('status', 'Outbound broadcast dispatched: n8n webhook sent and candidate notifications queued.');
    }
}
