<?php

namespace App\Models;

use App\Enums\NoticeStatus;
use App\Enums\NoticeType;
use Database\Factories\NoticeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $institution_id
 * @property string $source_id
 * @property string $source_artifact_id
 * @property NoticeType $notice_type
 * @property string $title
 * @property string $slug
 * @property string|null $reference_number
 * @property string|null $summary
 * @property NoticeStatus $status
 * @property float $confidence_score
 * @property bool $is_corrigendum
 * @property string|null $parent_notice_id
 * @property Carbon|null $published_at
 * @property Carbon|null $application_start_at
 * @property Carbon|null $application_end_at
 * @property Carbon|null $fee_payment_end_at
 * @property Carbon|null $correction_window_end_at
 * @property string|null $tentative_exam_date_text
 * @property Carbon|null $exam_start_at
 * @property Carbon|null $exam_end_at
 * @property string $canonical_source_url
 * @property int $total_vacancies
 * @property bool $is_featured
 * @property Carbon $first_seen_at
 * @property Carbon $last_seen_at
 * @property array<string, mixed> $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'institution_id',
    'source_id',
    'source_artifact_id',
    'notice_type',
    'title',
    'slug',
    'reference_number',
    'summary',
    'status',
    'confidence_score',
    'is_corrigendum',
    'parent_notice_id',
    'published_at',
    'application_start_at',
    'application_end_at',
    'fee_payment_end_at',
    'correction_window_end_at',
    'tentative_exam_date_text',
    'exam_start_at',
    'exam_end_at',
    'canonical_source_url',
    'total_vacancies',
    'is_featured',
    'first_seen_at',
    'last_seen_at',
    'metadata',
])]
class Notice extends Model
{
    /** @use HasFactory<NoticeFactory> */
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'notice_type' => NoticeType::class,
            'status' => NoticeStatus::class,
            'confidence_score' => 'float',
            'is_corrigendum' => 'boolean',
            'is_featured' => 'boolean',
            'total_vacancies' => 'integer',
            'published_at' => 'datetime',
            'application_start_at' => 'datetime',
            'application_end_at' => 'datetime',
            'fee_payment_end_at' => 'datetime',
            'correction_window_end_at' => 'datetime',
            'exam_start_at' => 'datetime',
            'exam_end_at' => 'datetime',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
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
     * @return BelongsTo<Source, $this>
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /**
     * @return BelongsTo<SourceArtifact, $this>
     */
    public function sourceArtifact(): BelongsTo
    {
        return $this->belongsTo(SourceArtifact::class);
    }

    /**
     * @return BelongsTo<Notice, $this>
     */
    public function parentNotice(): BelongsTo
    {
        return $this->belongsTo(Notice::class, 'parent_notice_id');
    }

    /**
     * @return HasMany<Notice, $this>
     */
    public function corrigenda(): HasMany
    {
        return $this->hasMany(Notice::class, 'parent_notice_id');
    }

    /**
     * @return HasMany<Position, $this>
     */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    /**
     * @return HasOne<EligibilityRule, $this>
     */
    public function eligibilityRule(): HasOne
    {
        return $this->hasOne(EligibilityRule::class);
    }

    /**
     * @return BelongsToMany<Qualification, $this>
     */
    public function qualifications(): BelongsToMany
    {
        return $this->belongsToMany(Qualification::class, 'notice_qualifications')
            ->withPivot(['is_mandatory', 'min_percentage']);
    }

    /**
     * @return HasOne<ApplicationDetail, $this>
     */
    public function applicationDetail(): HasOne
    {
        return $this->hasOne(ApplicationDetail::class);
    }

    /**
     * @return HasMany<Evidence, $this>
     */
    public function evidence(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }

    /**
     * @return HasMany<NoticeRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(NoticeRevision::class)->orderBy('version_number', 'desc');
    }

    /**
     * @return HasMany<ModerationReview, $this>
     */
    public function moderationReviews(): HasMany
    {
        return $this->hasMany(ModerationReview::class)->orderBy('created_at', 'desc');
    }

    /**
     * @return HasOne<Post, $this>
     */
    public function post(): HasOne
    {
        return $this->hasOne(Post::class);
    }
}
