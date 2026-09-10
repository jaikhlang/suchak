<?php

namespace App\Models;

use App\Enums\CrawlMethod;
use App\Enums\SourceStatus;
use App\Enums\SourceType;
use App\Enums\TrustLevel;
use Database\Factories\SourceFactory;
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
 * @property string $institution_id
 * @property string $name
 * @property SourceType $type
 * @property string $url
 * @property string|null $canonical_url
 * @property string $domain
 * @property CrawlMethod $crawl_method
 * @property int $crawl_frequency_minutes
 * @property string $status
 * @property TrustLevel $trust_level
 * @property int $consecutive_failures
 * @property Carbon|null $last_crawled_at
 * @property Carbon|null $next_crawl_at
 * @property Carbon|null $last_success_at
 * @property Carbon|null $last_failure_at
 * @property array<string, mixed> $configuration
 * @property array<string, mixed> $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'institution_id',
    'name',
    'type',
    'url',
    'canonical_url',
    'domain',
    'crawl_method',
    'crawl_frequency_minutes',
    'status',
    'trust_level',
    'consecutive_failures',
    'last_crawled_at',
    'next_crawl_at',
    'last_success_at',
    'last_failure_at',
    'configuration',
    'metadata',
])]
class Source extends Model
{
    /** @use HasFactory<SourceFactory> */
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => SourceType::class,
            'crawl_method' => CrawlMethod::class,
            'trust_level' => TrustLevel::class,
            'status' => SourceStatus::class,
            'crawl_frequency_minutes' => 'integer',
            'consecutive_failures' => 'integer',
            'last_crawled_at' => 'datetime',
            'next_crawl_at' => 'datetime',
            'last_success_at' => 'datetime',
            'last_failure_at' => 'datetime',
            'configuration' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Institution, $this>
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * @return HasMany<CrawlRun, $this>
     */
    public function crawlRuns(): HasMany
    {
        return $this->hasMany(CrawlRun::class);
    }

    /**
     * @return HasMany<SourceArtifact, $this>
     */
    public function artifacts(): HasMany
    {
        return $this->hasMany(SourceArtifact::class);
    }

    /**
     * @param  Builder<Source>  $query
     * @return Builder<Source>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * @param  Builder<Source>  $query
     * @return Builder<Source>
     */
    public function scopeDueForCrawl(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where(function (Builder $q) {
                $q->whereNull('next_crawl_at')
                    ->orWhere('next_crawl_at', '<=', now());
            });
    }
}
