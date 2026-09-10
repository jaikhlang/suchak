<?php

namespace App\Models;

use Database\Factories\NoticeRevisionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $notice_id
 * @property int $version_number
 * @property string $change_type
 * @property string|null $change_reason
 * @property string|null $triggering_artifact_id
 * @property array<string, mixed> $snapshot_data
 * @property array<string, mixed> $diff_data
 * @property int|null $created_by_user_id
 * @property Carbon $created_at
 */
#[Fillable([
    'notice_id',
    'version_number',
    'change_type',
    'change_reason',
    'triggering_artifact_id',
    'snapshot_data',
    'diff_data',
    'created_by_user_id',
])]
class NoticeRevision extends Model
{
    /** @use HasFactory<NoticeRevisionFactory> */
    use HasFactory, HasUlids;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'snapshot_data' => 'array',
            'diff_data' => 'array',
            'created_by_user_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Notice, $this>
     */
    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class);
    }

    /**
     * @return BelongsTo<SourceArtifact, $this>
     */
    public function triggeringArtifact(): BelongsTo
    {
        return $this->belongsTo(SourceArtifact::class, 'triggering_artifact_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
