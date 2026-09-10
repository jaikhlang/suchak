<?php

namespace App\Models;

use Database\Factories\EvidenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $artifact_id
 * @property string $extraction_id
 * @property string $notice_id
 * @property string $field_name
 * @property string $extracted_value
 * @property int|null $page_number
 * @property string $verbatim_text_fragment
 * @property int|null $char_start_offset
 * @property int|null $char_end_offset
 * @property array<string, mixed>|null $bounding_box
 * @property float $confidence_score
 * @property bool $is_verified_by_human
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'artifact_id',
    'extraction_id',
    'notice_id',
    'field_name',
    'extracted_value',
    'page_number',
    'verbatim_text_fragment',
    'char_start_offset',
    'char_end_offset',
    'bounding_box',
    'confidence_score',
    'is_verified_by_human',
])]
class Evidence extends Model
{
    /** @use HasFactory<EvidenceFactory> */
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'page_number' => 'integer',
            'char_start_offset' => 'integer',
            'char_end_offset' => 'integer',
            'bounding_box' => 'array',
            'confidence_score' => 'float',
            'is_verified_by_human' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<SourceArtifact, $this>
     */
    public function artifact(): BelongsTo
    {
        return $this->belongsTo(SourceArtifact::class, 'artifact_id');
    }

    /**
     * @return BelongsTo<ArtifactExtraction, $this>
     */
    public function extraction(): BelongsTo
    {
        return $this->belongsTo(ArtifactExtraction::class, 'extraction_id');
    }

    /**
     * @return BelongsTo<Notice, $this>
     */
    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class);
    }
}
