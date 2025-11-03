<?php

namespace App\Services\News;

use App\Models\NewsSource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class NewsSourceService
{
    public function create(array $attributes): NewsSource
    {
        return DB::transaction(function () use ($attributes): NewsSource {
            $normalized = $this->normalizeAttributes($attributes);

            return NewsSource::query()->create($normalized);
        });
    }

    public function update(NewsSource $source, array $attributes): NewsSource
    {
        return DB::transaction(function () use ($source, $attributes): NewsSource {
            $normalized = $this->normalizeAttributes($attributes);

            $source->fill($normalized);
            $source->save();

            return $source->refresh();
        });
    }

    public function delete(NewsSource $source): void
    {
        DB::transaction(static function () use ($source): void {
            $source->delete();
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function normalizeAttributes(array $attributes): array
    {
        $attributes['selector_type'] = strtolower($attributes['selector_type'] ?? 'css');
        $attributes['source_type'] = strtolower($attributes['source_type'] ?? 'rss');
        $attributes['fetch_frequency'] = $attributes['fetch_frequency'] ?? 'hourly';

        if (array_key_exists('keywords', $attributes)) {
            $attributes['keywords'] = $this->normalizeKeywords($attributes['keywords']);
        }

        if (array_key_exists('is_active', $attributes)) {
            $attributes['is_active'] = (bool) $attributes['is_active'];
        }

        if ($attributes['selector_type'] !== 'css' && $attributes['selector_type'] !== 'xpath') {
            $attributes['selector_type'] = 'css';
        }

        return Arr::except($attributes, ['id', 'created_at', 'updated_at']);
    }

    /**
     * @param  array<int, string>|string|null  $keywords
     */
    protected function normalizeKeywords(array|string|null $keywords): ?string
    {
        if ($keywords === null) {
            return null;
        }

        if (is_string($keywords)) {
            $keywords = explode(',', $keywords);
        }

        $normalized = collect($keywords)
            ->map(static fn (string $keyword): string => trim($keyword))
            ->filter()
            ->map(static fn (string $keyword): string => mb_strtolower($keyword))
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return null;
        }

        return $normalized->implode(', ');
    }
}
