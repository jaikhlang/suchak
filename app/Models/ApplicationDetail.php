<?php

namespace App\Models;

use Database\Factories\ApplicationDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $notice_id
 * @property string $application_mode
 * @property string|null $apply_url
 * @property string|null $official_notification_pdf_url
 * @property int $general_fee
 * @property int $reserved_fee
 * @property int $female_fee
 * @property bool $is_exempted_for_sc_st
 * @property bool $is_exempted_for_female
 * @property bool $is_exempted_for_pwbd
 * @property string|null $offline_postal_address
 * @property string|null $postal_pincode
 * @property string|null $instructions
 * @property array<string, mixed> $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'notice_id',
    'application_mode',
    'apply_url',
    'official_notification_pdf_url',
    'general_fee',
    'reserved_fee',
    'female_fee',
    'is_exempted_for_sc_st',
    'is_exempted_for_female',
    'is_exempted_for_pwbd',
    'offline_postal_address',
    'postal_pincode',
    'instructions',
    'metadata',
])]
class ApplicationDetail extends Model
{
    /** @use HasFactory<ApplicationDetailFactory> */
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'general_fee' => 'integer',
            'reserved_fee' => 'integer',
            'female_fee' => 'integer',
            'is_exempted_for_sc_st' => 'boolean',
            'is_exempted_for_female' => 'boolean',
            'is_exempted_for_pwbd' => 'boolean',
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
}
