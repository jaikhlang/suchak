<?php

namespace App\Models;

use Database\Factories\StateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string $iso_code
 * @property string $type
 * @property string|null $capital
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['name', 'iso_code', 'type', 'capital', 'is_active'])]
class State extends Model
{
    /** @use HasFactory<StateFactory> */
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<District, $this>
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    /**
     * @return HasMany<Institution, $this>
     */
    public function institutions(): HasMany
    {
        return $this->hasMany(Institution::class);
    }

    /**
     * @param  Builder<State>  $query
     * @return Builder<State>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
