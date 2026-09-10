<?php

namespace App\Models;

use Database\Factories\ModerationReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $notice_id
 * @property int $reviewer_id
 * @property string $action
 * @property string|null $notes
 * @property array<string, mixed> $field_corrections
 * @property Carbon $reviewed_at
 * @property Carbon $created_at
 */
#[Fillable([
    'notice_id',
    'reviewer_id',
    'action',
    'notes',
    'field_corrections',
    'reviewed_at',
])]
class ModerationReview extends Model
{
    /** @use HasFactory<ModerationReviewFactory> */
    use HasFactory, HasUlids;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reviewer_id' => 'integer',
            'field_corrections' => 'array',
            'reviewed_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
