<?php

namespace App\Http\Controllers;

use App\Models\NewsPost;
use App\Services\News\HomeFeedBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class NewsPostController extends Controller
{
    public function __construct(
        protected HomeFeedBuilder $feedBuilder,
    ) {}

    public function show(string $category, string $slug): View
    {
        $post = NewsPost::query()
            ->published()
            ->where('category_slug', $category)
            ->where('slug', $slug)
            ->firstOrFail();

        $homeSidebar = Cache::remember('home:payload', now()->addSeconds(60), fn () => $this->feedBuilder->build());

        $metaSource = $post->summary
            ?: $post->body_text
            ?: ($post->body_html !== null ? strip_tags($post->body_html) : '');

        $metaDescription = Str::limit(preg_replace('/\s+/', ' ', trim((string) $metaSource)), 180);

        $categoryName = $post->category_label
            ?? Str::title(str_replace(['-', '_'], ' ', $post->category_slug));

        return view('news-post', [
            'post' => $post,
            'metaDescription' => $metaDescription,
            'categoryName' => $categoryName,
            'sidebarHighlights' => $homeSidebar['sidebarHighlights'],
            'topics' => $homeSidebar['topics'],
            'sourcesList' => $homeSidebar['sourcesList'],
            'refreshedAt' => $homeSidebar['refreshedAt'],
        ]);
    }
}
