<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortLink extends Model
{
    /** @use HasFactory<\Database\Factories\ShortLinkFactory> */
    use HasFactory;

    protected $fillable = [
        'news_raw_id',
        'code',
        'target_url',
        'click_count',
        'last_clicked_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'click_count' => 'integer',
            'last_clicked_at' => 'datetime',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(NewsRaw::class, 'news_raw_id');
    }
}
