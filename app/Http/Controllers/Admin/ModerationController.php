<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Corrigendum\ApplyCorrigendumPatchAction;
use App\Actions\Moderation\ApproveNoticeAction;
use App\Actions\Moderation\RejectNoticeAction;
use App\Actions\Moderation\UpdateNoticeStructuredDataAction;
use App\Enums\NoticeStatus;
use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\ModerationReview;
use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModerationController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->input('status', 'pending_review');
        $confidenceTier = $request->input('confidence_tier');
        $institutionId = $request->input('institution_id');
        $search = $request->input('search');

        $query = Notice::with(['institution', 'source'])
            ->latest('first_seen_at');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($confidenceTier === 'high_risk') {
            $query->where('confidence_score', '<', 0.75);
        } elseif ($confidenceTier === 'standard') {
            $query->whereBetween('confidence_score', [0.75, 0.9399]);
        } elseif ($confidenceTier === 'auto_eligible') {
            $query->where('confidence_score', '>=', 0.94);
        }

        if ($institutionId) {
            $query->where('institution_id', $institutionId);
        }

        if ($search) {
            $searchLower = '%'.strtolower($search).'%';
            $query->where(function ($q) use ($searchLower) {
                $q->whereRaw('LOWER(title) LIKE ?', [$searchLower])
                    ->orWhereRaw('LOWER(reference_number) LIKE ?', [$searchLower]);
            });
        }

        $notices = $query->paginate(15)->withQueryString();

        // Queue Statistics
        $stats = [
            'total_pending' => Notice::where('status', NoticeStatus::PendingReview)->count(),
            'high_risk' => Notice::where('status', NoticeStatus::PendingReview)->where('confidence_score', '<', 0.75)->count(),
            'standard' => Notice::where('status', NoticeStatus::PendingReview)->whereBetween('confidence_score', [0.75, 0.9399])->count(),
            'auto_eligible' => Notice::where('status', NoticeStatus::PendingReview)->where('confidence_score', '>=', 0.94)->count(),
            'total_approved' => Notice::whereIn('status', [NoticeStatus::Approved, NoticeStatus::Published])->count(),
        ];

        $institutions = Institution::select('id', 'name', 'short_name')->orderBy('name')->get();

        return Inertia::render('admin/moderation/Index', [
            'notices' => $notices,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'confidence_tier' => $confidenceTier,
                'institution_id' => $institutionId,
                'search' => $search,
            ],
            'institutions' => $institutions,
        ]);
    }

    public function show(Notice $notice): Response
    {
        $notice->loadMissing([
            'institution',
            'source',
            'sourceArtifact.extractions',
            'positions.reservations',
            'evidence',
            'applicationDetail',
            'eligibilityRule',
            'revisions.user',
            'moderationReviews.reviewer',
        ]);

        // Find next notice in pending queue for fast speed-run cycling
        $nextPendingNotice = Notice::where('status', NoticeStatus::PendingReview)
            ->where('id', '!=', $notice->id)
            ->latest('first_seen_at')
            ->first(['id', 'title', 'slug']);

        $primaryExtraction = $notice->sourceArtifact?->extractions?->first();

        // Potential parent notices from the same institution for Corrigendum or Merge actions
        $candidateParentNotices = Notice::where('institution_id', $notice->institution_id)
            ->where('id', '!=', $notice->id)
            ->where('is_corrigendum', false)
            ->latest('first_seen_at')
            ->take(25)
            ->get(['id', 'title', 'reference_number', 'total_vacancies', 'application_end_at', 'status']);

        return Inertia::render('admin/moderation/Show', [
            'notice' => $notice,
            'primaryExtraction' => $primaryExtraction,
            'nextPendingNotice' => $nextPendingNotice,
            'candidateParentNotices' => $candidateParentNotices,
        ]);
    }

    public function approve(Request $request, Notice $notice, ApproveNoticeAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
            'publish_post' => ['nullable', 'boolean'],
        ]);

        $action->execute(
            $notice,
            $request->user(),
            $validated['notes'] ?? null,
            $request->boolean('publish_post', true)
        );

        $next = Notice::where('status', NoticeStatus::PendingReview)
            ->where('id', '!=', $notice->id)
            ->first();

        if ($next) {
            return redirect()->route('admin.moderation.show', $next)
                ->with('success', 'Notice approved successfully! Advanced to next pending notice.');
        }

        return redirect()->route('admin.moderation.index')
            ->with('success', 'Notice approved and published!');
    }

    public function reject(Request $request, Notice $notice, RejectNoticeAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $action->execute(
            $notice,
            $request->user(),
            $validated['reason'],
            $validated['notes'] ?? null
        );

        $next = Notice::where('status', NoticeStatus::PendingReview)
            ->where('id', '!=', $notice->id)
            ->first();

        if ($next) {
            return redirect()->route('admin.moderation.show', $next)
                ->with('success', 'Notice rejected. Advanced to next pending notice.');
        }

        return redirect()->route('admin.moderation.index')
            ->with('success', 'Notice has been rejected.');
    }

    public function update(Request $request, Notice $notice, UpdateNoticeStructuredDataAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:500'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'application_start_at' => ['nullable', 'date'],
            'application_end_at' => ['nullable', 'date'],
            'fee_payment_end_at' => ['nullable', 'date'],
            'correction_window_end_at' => ['nullable', 'date'],
            'tentative_exam_date_text' => ['nullable', 'string', 'max:255'],
            'total_vacancies' => ['nullable', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $action->execute($notice, $validated, $request->user(), $validated['reason'] ?? null);

        return back()->with('success', 'Notice details updated and revision logged.');
    }

    public function markCorrigendum(Request $request, Notice $notice, ApplyCorrigendumPatchAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'parent_notice_id' => ['required', 'exists:notices,id'],
            'reason' => ['nullable', 'string', 'max:500'],
            'application_end_at' => ['nullable', 'date'],
            'fee_payment_end_at' => ['nullable', 'date'],
            'correction_window_end_at' => ['nullable', 'date'],
            'tentative_exam_date_text' => ['nullable', 'string', 'max:255'],
            'total_vacancies' => ['nullable', 'integer', 'min:0'],
        ]);

        $parentNotice = Notice::findOrFail($validated['parent_notice_id']);

        $patchFields = array_filter([
            'application_end_at' => $validated['application_end_at'] ?? null,
            'fee_payment_end_at' => $validated['fee_payment_end_at'] ?? null,
            'correction_window_end_at' => $validated['correction_window_end_at'] ?? null,
            'tentative_exam_date_text' => $validated['tentative_exam_date_text'] ?? null,
            'total_vacancies' => isset($validated['total_vacancies']) && $validated['total_vacancies'] !== '' ? (int) $validated['total_vacancies'] : null,
        ], fn ($val) => $val !== null);

        $action->execute(
            $parentNotice,
            $notice,
            $patchFields,
            $request->user(),
            $validated['reason'] ?? null
        );

        $next = Notice::where('status', NoticeStatus::PendingReview)
            ->where('id', '!=', $notice->id)
            ->first();

        if ($next) {
            return redirect()->route('admin.moderation.show', $next)
                ->with('success', "Corrigendum patch applied to: {$parentNotice->title}! Advanced to next pending item.");
        }

        return redirect()->route('admin.moderation.index')
            ->with('success', "Corrigendum patch applied successfully to: {$parentNotice->title}");
    }

    public function mergeDuplicate(Request $request, Notice $notice): RedirectResponse
    {
        $validated = $request->validate([
            'canonical_notice_id' => ['required', 'exists:notices,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $canonicalNotice = Notice::findOrFail($validated['canonical_notice_id']);

        // Merge duplicate: mark this notice rejected as duplicate and reference canonical ID in metadata
        $metadata = $notice->metadata ?? [];
        $metadata['duplicate_of_notice_id'] = $canonicalNotice->id;
        $metadata['merged_at'] = now()->toIso8601String();

        $notice->update([
            'status' => NoticeStatus::Rejected,
            'metadata' => $metadata,
        ]);

        ModerationReview::create([
            'notice_id' => $notice->id,
            'reviewer_id' => $request->user()->id,
            'action' => 'merge_duplicate',
            'notes' => $validated['notes'] ?? "Merged as duplicate of {$canonicalNotice->reference_number} ({$canonicalNotice->id})",
            'field_corrections' => ['canonical_notice_id' => $canonicalNotice->id],
            'reviewed_at' => now(),
        ]);

        $next = Notice::where('status', NoticeStatus::PendingReview)
            ->where('id', '!=', $notice->id)
            ->first();

        if ($next) {
            return redirect()->route('admin.moderation.show', $next)
                ->with('success', "Notice merged as duplicate into: {$canonicalNotice->title}! Advanced to next item.");
        }

        return redirect()->route('admin.moderation.index')
            ->with('success', "Notice merged as duplicate into: {$canonicalNotice->title}");
    }
}
