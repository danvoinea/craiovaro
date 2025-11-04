<?php

namespace App\Services\News;

use App\Models\NewsPost;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsPostService
{
    public function create(array $attributes): NewsPost
    {
        return DB::transaction(function () use ($attributes): NewsPost {
            $normalized = $this->normalizeAttributes($attributes);

            return NewsPost::query()->create($normalized);
        });
    }

    public function update(NewsPost $post, array $attributes): NewsPost
    {
        return DB::transaction(function () use ($post, $attributes): NewsPost {
            $normalized = $this->normalizeAttributes($attributes, $post);

            $post->fill($normalized);
            $post->save();

            return $post->refresh();
        });
    }

    public function delete(NewsPost $post): void
    {
        DB::transaction(static function () use ($post): void {
            $post->delete();
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function normalizeAttributes(array $attributes, ?NewsPost $post = null): array
    {
        if (isset($attributes['title'])) {
            $attributes['title'] = trim((string) $attributes['title']);
        }

        if (array_key_exists('category_slug', $attributes)) {
            $attributes['category_slug'] = Str::slug((string) $attributes['category_slug']);
        } elseif ($post !== null) {
            $attributes['category_slug'] = $post->category_slug;
        }

        if (! isset($attributes['category_label']) || $attributes['category_label'] === '') {
            $categoryForLabel = $attributes['category_slug'] ?? $post?->category_slug;

            if ($categoryForLabel !== null) {
                $readable = str_replace(['-', '_'], ' ', $categoryForLabel);
                $attributes['category_label'] = Str::title($readable);
            }
        }

        if (! isset($attributes['slug']) || $attributes['slug'] === '') {
            $slugSource = $attributes['title'] ?? ($post !== null ? $post->title : Str::random(12));
            $attributes['slug'] = Str::slug((string) $slugSource);
        } else {
            $attributes['slug'] = Str::slug((string) $attributes['slug']);
        }

        if (array_key_exists('summary', $attributes)) {
            $attributes['summary'] = $attributes['summary'] !== null
                ? trim((string) $attributes['summary'])
                : null;
        }

        if (array_key_exists('body_html', $attributes)) {
            $attributes['body_html'] = $attributes['body_html'] !== null
                ? trim((string) $attributes['body_html'])
                : null;

            $attributes['body_text'] = $attributes['body_html'] !== null
                ? trim(strip_tags($attributes['body_html']))
                : null;
        }

        if (array_key_exists('hero_image_url', $attributes) && $attributes['hero_image_url'] !== null) {
            $attributes['hero_image_url'] = trim((string) $attributes['hero_image_url']);
        }

        if (array_key_exists('published_at', $attributes)) {
            $attributes['published_at'] = Carbon::parse($attributes['published_at']);
        }

        if (array_key_exists('is_highlighted', $attributes)) {
            $attributes['is_highlighted'] = (bool) $attributes['is_highlighted'];
        }

        if (array_key_exists('is_published', $attributes)) {
            $attributes['is_published'] = (bool) $attributes['is_published'];
        }

        return Arr::except($attributes, ['id', 'created_at', 'updated_at']);
    }
}
