<?php

namespace App\Models;

use Database\Factories\ArtifactExtractionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $artifact_id
 * @property string $method
 * @property string $status
 * @property string|null $raw_text
 * @property string|null $clean_text
 * @property int|null $page_count
 * @property float|null $confidence_score
 * @property string $processor_name
 * @property Carbon $started_at
 * @property Carbon|null $finished_at
 * @property array<string, mixed> $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'artifact_id',
    'method',
    'status',
    'raw_text',
    'clean_text',
    'page_count',
    'confidence_score',
    'processor_name',
    'started_at',
    'finished_at',
    'metadata',
])]
class ArtifactExtraction extends Model
{
    /** @use HasFactory<ArtifactExtractionFactory> */
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'page_count' => 'integer',
            'confidence_score' => 'float',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<SourceArtifact, $this>
     */
    public function artifact(): BelongsTo
    {
        return $this->belongsTo(SourceArtifact::class, 'artifact_id');
    }
}
