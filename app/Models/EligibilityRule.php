<?php

namespace App\Models;

use Database\Factories\EligibilityRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $notice_id
 * @property int|null $minimum_age
 * @property int|null $maximum_age
 * @property Carbon|null $age_calculated_as_on
 * @property array<string, mixed> $age_relaxation_json
 * @property string|null $qualification_summary
 * @property string|null $experience_text
 * @property string $nationality_text
 * @property string|null $raw_eligibility_text
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'notice_id',
    'minimum_age',
    'maximum_age',
    'age_calculated_as_on',
    'age_relaxation_json',
    'qualification_summary',
    'experience_text',
    'nationality_text',
    'raw_eligibility_text',
])]
class EligibilityRule extends Model
{
    /** @use HasFactory<EligibilityRuleFactory> */
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'minimum_age' => 'integer',
            'maximum_age' => 'integer',
            'age_calculated_as_on' => 'date',
            'age_relaxation_json' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Notice, $this>
     */
    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class);
    }
}
