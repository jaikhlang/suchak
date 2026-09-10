<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\CrawlRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $source_id
 * @property string $status
 * @property CarbonInterface $started_at
 * @property CarbonInterface|null $finished_at
 * @property int $items_discovered
 * @property int $items_fetched
 * @property int $items_failed
 * @property int|null $http_status
 * @property string|null $error_code
 * @property string|null $error_message
 * @property array<string, mixed> $metadata
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 */
#[Fillable([
    'source_id',
    'status',
    'started_at',
    'finished_at',
    'items_discovered',
    'items_fetched',
    'items_failed',
    'http_status',
    'error_code',
    'error_message',
    'metadata',
])]
class CrawlRun extends Model
{
    /** @use HasFactory<CrawlRunFactory> */
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'items_discovered' => 'integer',
            'items_fetched' => 'integer',
            'items_failed' => 'integer',
            'http_status' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Source, $this>
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /**
     * @return HasMany<SourceArtifact, $this>
     */
    public function artifacts(): HasMany
    {
        return $this->hasMany(SourceArtifact::class);
    }
}
