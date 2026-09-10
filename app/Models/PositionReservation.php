<?php

namespace App\Models;

use App\Enums\QuotaType;
use App\Enums\ReservationCategory;
use Database\Factories\PositionReservationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $position_id
 * @property ReservationCategory $category
 * @property QuotaType $quota_type
 * @property int $vacancies
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'position_id',
    'category',
    'quota_type',
    'vacancies',
])]
class PositionReservation extends Model
{
    /** @use HasFactory<PositionReservationFactory> */
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => ReservationCategory::class,
            'quota_type' => QuotaType::class,
            'vacancies' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Position, $this>
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
