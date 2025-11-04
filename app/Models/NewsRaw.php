<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $title
 * @property array<string, mixed>|null $meta
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property-read NewsSource|null $source
 * @property-read ShortLink|null $shortLink
 */
class NewsRaw extends Model
{
    /** @use HasFactory<\Database\Factories\NewsRawFactory> */
    use HasFactory;

    protected $table = 'news_raw';

    protected $fillable = [
        'news_source_id',
        'title',
        'body_html',
        'body_text',
        'published_at',
        'source_name',
        'source_url',
        'cover_image_url',
        'url_hash',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(NewsSource::class, 'news_source_id');
    }

    public function shortLink(): HasOne
    {
        return $this->hasOne(ShortLink::class);
    }
}
