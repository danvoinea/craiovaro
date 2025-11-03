<?php

namespace App\Services\Links;

use App\Models\NewsRaw;
use App\Models\ShortLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShortLinkService
{
    public function getOrCreateForArticle(NewsRaw $article): ShortLink
    {
        if ($article->shortLink) {
            return $article->shortLink;
        }

        return DB::transaction(function () use ($article): ShortLink {
            $existing = $article->shortLink()->lockForUpdate()->first();

            if ($existing !== null) {
                return $existing;
            }

            $code = $this->generateUniqueCode();

            return $article->shortLink()->create([
                'code' => $code,
                'target_url' => $article->source_url,
            ]);
        });
    }

    protected function generateUniqueCode(): string
    {
        do {
            $code = Str::lower(Str::random(6));
        } while (ShortLink::query()->where('code', $code)->exists());

        return $code;
    }

    public function recordClick(ShortLink $shortLink): void
    {
        ShortLink::query()
            ->whereKey($shortLink->getKey())
            ->update([
                'click_count' => DB::raw('click_count + 1'),
                'last_clicked_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
