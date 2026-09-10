<?php

namespace App\Models;

use App\Enums\EmploymentType;
use Database\Factories\PositionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $notice_id
 * @property string $title
 * @property string|null $post_code
 * @property string|null $department
 * @property int $total_vacancies
 * @property EmploymentType $employment_type
 * @property string|null $pay_level
 * @property string|null $pay_scale_text
 * @property int|null $salary_min
 * @property int|null $salary_max
 * @property array<string, mixed> $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'notice_id',
    'title',
    'post_code',
    'department',
    'total_vacancies',
    'employment_type',
    'pay_level',
    'pay_scale_text',
    'salary_min',
    'salary_max',
    'metadata',
])]
class Position extends Model
{
    /** @use HasFactory<PositionFactory> */
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'employment_type' => EmploymentType::class,
            'total_vacancies' => 'integer',
            'salary_min' => 'integer',
            'salary_max' => 'integer',
            'metadata' => 'array',
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
     * @return HasMany<PositionReservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(PositionReservation::class);
    }
}
