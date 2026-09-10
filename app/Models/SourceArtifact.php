<?php

namespace App\Models;

use Database\Factories\SourceArtifactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $source_id
 * @property string $crawl_run_id
 * @property string $type
 * @property string $url
 * @property string $canonical_url
 * @property string $content_hash
 * @property string|null $etag
 * @property string|null $last_modified_header
 * @property string $mime_type
 * @property int $http_status
 * @property string $storage_disk
 * @property string $storage_path
 * @property int $file_size_bytes
 * @property string|null $title
 * @property Carbon $retrieved_at
 * @property array<string, mixed> $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'source_id',
    'crawl_run_id',
    'type',
    'url',
    'canonical_url',
    'content_hash',
    'etag',
    'last_modified_header',
    'mime_type',
    'http_status',
    'storage_disk',
    'storage_path',
    'file_size_bytes',
    'title',
    'retrieved_at',
    'metadata',
])]
class SourceArtifact extends Model
{
    /** @use HasFactory<SourceArtifactFactory> */
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'http_status' => 'integer',
            'file_size_bytes' => 'integer',
            'retrieved_at' => 'datetime',
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
     * @return BelongsTo<CrawlRun, $this>
     */
    public function crawlRun(): BelongsTo
    {
        return $this->belongsTo(CrawlRun::class);
    }

    /**
     * @return HasMany<ArtifactExtraction, $this>
     */
    public function extractions(): HasMany
    {
        return $this->hasMany(ArtifactExtraction::class, 'artifact_id');
    }

    /**
     * @return HasMany<Notice, $this>
     */
    public function notices(): HasMany
    {
        return $this->hasMany(Notice::class, 'source_artifact_id');
    }
}
