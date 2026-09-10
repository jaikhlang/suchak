<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $notice_id
 * @property string $title
 * @property string $slug
 * @property string $excerpt
 * @property string $content_html
 * @property string $status
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property string $canonical_url
 * @property Carbon|null $published_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'notice_id',
    'title',
    'slug',
    'excerpt',
    'content_html',
    'status',
    'seo_title',
    'seo_description',
    'canonical_url',
    'published_at',
])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasUlids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Notice, $this>
     */
    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class);
    }
}
