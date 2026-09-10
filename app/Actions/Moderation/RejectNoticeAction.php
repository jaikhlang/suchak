<?php

namespace App\Actions\Moderation;

use App\Enums\NoticeStatus;
use App\Models\ModerationReview;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RejectNoticeAction
{
    public function execute(Notice $notice, User $reviewer, string $reason, ?string $notes = null): Notice
    {
        return DB::transaction(function () use ($notice, $reviewer, $reason, $notes) {
            $metadata = $notice->metadata ?? [];
            $metadata['rejection_reason'] = $reason;

            $notice->update([
                'status' => NoticeStatus::Rejected,
                'metadata' => $metadata,
            ]);

            ModerationReview::create([
                'notice_id' => $notice->id,
                'reviewer_id' => $reviewer->id,
                'action' => 'reject',
                'notes' => $notes ?: $reason,
                'field_corrections' => ['reason' => $reason],
                'reviewed_at' => now(),
            ]);

            return $notice->fresh();
        });
    }
}
