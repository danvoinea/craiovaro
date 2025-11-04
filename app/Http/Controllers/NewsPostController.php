<?php

namespace App\Http\Controllers;

use App\Models\NewsPost;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class NewsPostController extends Controller
{
    public function show(string $category, string $slug): View
    {
        $post = NewsPost::query()
            ->published()
            ->where('category_slug', $category)
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedPosts = NewsPost::query()
            ->published()
            ->where('category_slug', $post->category_slug)
            ->whereKeyNot($post->getKey())
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        $metaSource = $post->summary
            ?: $post->body_text
            ?: ($post->body_html !== null ? strip_tags($post->body_html) : '');

        $metaDescription = Str::limit(preg_replace('/\s+/', ' ', trim((string) $metaSource)), 180);

        $categoryName = $post->category_label
            ?? Str::title(str_replace(['-', '_'], ' ', $post->category_slug));

        return view('news-post', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'metaDescription' => $metaDescription,
            'categoryName' => $categoryName,
        ]);
    }
}
