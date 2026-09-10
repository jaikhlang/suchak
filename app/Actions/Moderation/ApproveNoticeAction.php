<?php

namespace App\Actions\Moderation;

use App\Actions\Publishing\GeneratePublicPostAction;
use App\Enums\NoticeStatus;
use App\Events\NoticePublished;
use App\Models\ModerationReview;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApproveNoticeAction
{
    public function __construct(
        protected GeneratePublicPostAction $postGenerator
    ) {}

    public function execute(Notice $notice, User $reviewer, ?string $notes = null, bool $publishPost = true): Notice
    {
        return DB::transaction(function () use ($notice, $reviewer, $notes, $publishPost) {
            $status = $publishPost ? NoticeStatus::Published : NoticeStatus::Approved;

            $notice->update([
                'status' => $status,
            ]);

            ModerationReview::create([
                'notice_id' => $notice->id,
                'reviewer_id' => $reviewer->id,
                'action' => 'approve',
                'notes' => $notes,
                'field_corrections' => [],
                'reviewed_at' => now(),
            ]);

            if ($publishPost) {
                $this->postGenerator->execute($notice);
                event(new NoticePublished($notice));
            }

            return $notice->fresh(['post', 'institution']);
        });
    }
}
