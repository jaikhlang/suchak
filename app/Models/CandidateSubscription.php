<?php

namespace App\Models;

use Database\Factories\CandidateSubscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $email
 * @property string|null $state_id
 * @property string|null $institution_id
 * @property string|null $reservation_category
 * @property bool $is_verified
 * @property string|null $verification_token
 * @property Carbon|null $last_notified_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'email',
    'state_id',
    'institution_id',
    'reservation_category',
    'is_verified',
    'verification_token',
    'last_notified_at',
])]
class CandidateSubscription extends Model
{
    /** @use HasFactory<CandidateSubscriptionFactory> */
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'last_notified_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<State, $this>
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * @return BelongsTo<Institution, $this>
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
