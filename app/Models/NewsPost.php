<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $category_slug
 * @property string|null $category_label
 * @property string|null $summary
 * @property string|null $body_html
 * @property string|null $body_text
 * @property string|null $hero_image_url
 * @property Carbon $published_at
 * @property bool $is_highlighted
 * @property bool $is_published
 */
class NewsPost extends Model
{
    /** @use HasFactory<\Database\Factories\NewsPostFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category_slug',
        'category_label',
        'summary',
        'body_html',
        'body_text',
        'hero_image_url',
        'published_at',
        'is_highlighted',
        'is_published',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_highlighted' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    public function scopeHighlighted(Builder $query): Builder
    {
        return $query->where('is_highlighted', true);
    }
}
