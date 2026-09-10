<?php

namespace App\Actions\Corrigendum;

use App\Actions\Publishing\GeneratePublicPostAction;
use App\Enums\NoticeStatus;
use App\Models\Notice;
use App\Models\NoticeRevision;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApplyCorrigendumPatchAction
{
    public function __construct(
        protected GeneratePublicPostAction $postGenerator
    ) {}

    /**
     * @param  array<string, mixed>  $patchFields
     */
    public function execute(
        Notice $parentNotice,
        Notice $childCorrigendum,
        array $patchFields,
        ?User $actor = null,
        ?string $reason = null
    ): Notice {
        return DB::transaction(function () use ($parentNotice, $childCorrigendum, $patchFields, $actor, $reason) {
            $allowedPatchKeys = [
                'application_end_at',
                'fee_payment_end_at',
                'correction_window_end_at',
                'exam_start_at',
                'exam_end_at',
                'tentative_exam_date_text',
                'total_vacancies',
            ];

            $diffData = [];
            $snapshotData = $parentNotice->only($allowedPatchKeys);

            foreach ($allowedPatchKeys as $key) {
                if (array_key_exists($key, $patchFields)) {
                    $oldVal = $parentNotice->{$key};
                    $newVal = $patchFields[$key];

                    if ($oldVal != $newVal) {
                        $diffData[$key] = [
                            'old' => $oldVal,
                            'new' => $newVal,
                        ];
                        $parentNotice->{$key} = $newVal;
                    }
                }
            }

            // Create revision audit on parent notice
            $lastVersion = (int) $parentNotice->revisions()->max('version_number');
            $newVersion = $lastVersion + 1;

            $changeReason = $reason ?: "Corrigendum patch applied from: {$childCorrigendum->reference_number} ({$childCorrigendum->title})";

            NoticeRevision::create([
                'notice_id' => $parentNotice->id,
                'version_number' => $newVersion,
                'change_type' => 'corrigendum_patch',
                'change_reason' => $changeReason,
                'triggering_artifact_id' => $childCorrigendum->source_artifact_id,
                'snapshot_data' => $snapshotData,
                'diff_data' => $diffData,
                'created_by_user_id' => $actor?->id,
                'created_at' => now(),
            ]);

            // Save parent notice modifications
            $parentNotice->save();

            // Link child corrigendum
            $childCorrigendum->update([
                'parent_notice_id' => $parentNotice->id,
                'is_corrigendum' => true,
                'status' => NoticeStatus::Approved,
            ]);

            // Regenerate public post if parent is published
            if ($parentNotice->status === NoticeStatus::Published || $parentNotice->post()->exists()) {
                $this->postGenerator->execute($parentNotice);
            }

            return $parentNotice->fresh(['revisions', 'corrigenda', 'post']);
        });
    }
}
