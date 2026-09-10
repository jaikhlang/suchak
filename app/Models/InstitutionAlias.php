<?php

namespace App\Models;

use Database\Factories\InstitutionAliasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $institution_id
 * @property string $alias
 * @property string $locale
 * @property bool $is_primary
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['institution_id', 'alias', 'locale', 'is_primary'])]
class InstitutionAlias extends Model
{
    /** @use HasFactory<InstitutionAliasFactory> */
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Institution, $this>
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
