<?php

namespace App\Actions\Moderation;

use App\Models\Evidence;
use App\Models\ModerationReview;
use App\Models\Notice;
use App\Models\NoticeRevision;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateNoticeStructuredDataAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Notice $notice, array $data, User $reviewer, ?string $reason = null): Notice
    {
        return DB::transaction(function () use ($notice, $data, $reviewer, $reason) {
            $allowedFields = [
                'title',
                'reference_number',
                'summary',
                'application_start_at',
                'application_end_at',
                'fee_payment_end_at',
                'correction_window_end_at',
                'tentative_exam_date_text',
                'exam_start_at',
                'total_vacancies',
            ];

            $diffData = [];
            $snapshotData = $notice->only($allowedFields);

            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $oldVal = $notice->{$field};
                    $newVal = $data[$field];

                    if ($oldVal != $newVal) {
                        $diffData[$field] = [
                            'old' => $oldVal,
                            'new' => $newVal,
                        ];
                        $notice->{$field} = $newVal;
                    }
                }
            }

            if (! empty($diffData)) {
                $lastVersion = (int) $notice->revisions()->max('version_number');
                $newVersion = $lastVersion + 1;

                NoticeRevision::create([
                    'notice_id' => $notice->id,
                    'version_number' => $newVersion,
                    'change_type' => 'manual_edit',
                    'change_reason' => $reason ?: 'Moderator manual field correction',
                    'triggering_artifact_id' => $notice->source_artifact_id,
                    'snapshot_data' => $snapshotData,
                    'diff_data' => $diffData,
                    'created_by_user_id' => $reviewer->id,
                    'created_at' => now(),
                ]);

                ModerationReview::create([
                    'notice_id' => $notice->id,
                    'reviewer_id' => $reviewer->id,
                    'action' => 'apply_manual_patch',
                    'notes' => $reason ?: 'Manual field corrections applied',
                    'field_corrections' => $diffData,
                    'reviewed_at' => now(),
                ]);

                // Update human verification on touched evidence
                foreach (array_keys($diffData) as $changedField) {
                    Evidence::where('notice_id', $notice->id)
                        ->where('field_name', $changedField)
                        ->update([
                            'extracted_value' => (string) ($data[$changedField] ?? ''),
                            'is_verified_by_human' => true,
                        ]);
                }

                $notice->save();
            }

            return $notice->fresh(['positions.reservations', 'evidence', 'applicationDetail', 'institution']);
        });
    }
}
