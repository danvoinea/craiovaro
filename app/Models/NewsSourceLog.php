<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsSourceLog extends Model
{
    /** @use HasFactory<\Database\Factories\NewsSourceLogFactory> */
    use HasFactory;

    protected $table = 'news_source_logs';

    protected $fillable = [
        'news_source_id',
        'status',
        'message',
        'ran_at',
        'duration_ms',
        'context',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ran_at' => 'datetime',
            'context' => 'array',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(NewsSource::class);
    }
}
