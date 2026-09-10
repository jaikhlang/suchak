<?php

namespace App\Models;

use Database\Factories\QualificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string $level
 * @property string|null $discipline
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'name',
    'slug',
    'level',
    'discipline',
])]
class Qualification extends Model
{
    /** @use HasFactory<QualificationFactory> */
    use HasFactory, HasUlids;

    /**
     * @return BelongsToMany<Notice, $this>
     */
    public function notices(): BelongsToMany
    {
        return $this->belongsToMany(Notice::class, 'notice_qualifications')
            ->withPivot(['is_mandatory', 'min_percentage']);
    }
}
