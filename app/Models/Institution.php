<?php

namespace App\Models;

use App\Enums\InstitutionType;
use Database\Factories\InstitutionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string|null $short_name
 * @property string $slug
 * @property InstitutionType $institution_type
 * @property string|null $parent_id
 * @property string|null $state_id
 * @property string $website_url
 * @property string $official_domain
 * @property bool $is_verified
 * @property bool $is_active
 * @property array<string, mixed> $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'name',
    'short_name',
    'slug',
    'institution_type',
    'parent_id',
    'state_id',
    'website_url',
    'official_domain',
    'is_verified',
    'is_active',
    'metadata',
])]
class Institution extends Model
{
    /** @use HasFactory<InstitutionFactory> */
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'institution_type' => InstitutionType::class,
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'metadata' => 'array',
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
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Institution::class, 'parent_id');
    }

    /**
     * @return HasMany<Institution, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Institution::class, 'parent_id');
    }

    /**
     * @return HasMany<InstitutionAlias, $this>
     */
    public function aliases(): HasMany
    {
        return $this->hasMany(InstitutionAlias::class);
    }

    /**
     * @return HasMany<Source, $this>
     */
    public function sources(): HasMany
    {
        return $this->hasMany(Source::class);
    }

    /**
     * @return HasMany<Notice, $this>
     */
    public function notices(): HasMany
    {
        return $this->hasMany(Notice::class);
    }

    /**
     * @param  Builder<Institution>  $query
     * @return Builder<Institution>
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    /**
     * @param  Builder<Institution>  $query
     * @return Builder<Institution>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<Institution>  $query
     * @return Builder<Institution>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'ilike', "%{$term}%")
                ->orWhere('short_name', 'ilike', "%{$term}%")
                ->orWhere('official_domain', 'ilike', "%{$term}%")
                ->orWhereHas('aliases', fn (Builder $sub) => $sub->where('alias', 'ilike', "%{$term}%"));
        });
    }
}
