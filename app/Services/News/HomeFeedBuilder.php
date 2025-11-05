<?php

namespace App\Services\News;

use App\Models\NewsPost;
use App\Models\NewsRaw;
use App\Models\NewsSource;
use App\Services\Links\ShortLinkService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HomeFeedBuilder
{
    public function __construct(
        protected ShortLinkService $shortLinks,
    ) {}

    /**
     * @return array{
     *     currentNews: Collection<int, array<string, mixed>>,
     *     sidebarHighlights: Collection<int, array<string, mixed>>,
     *     topics: Collection<int, array<string, mixed>>,
     *     sourcesList: Collection<int, array{name: string, url: string}>,
     *     refreshedAt: Carbon
     * }
     */
    public function build(): array
    {
        $threshold = Carbon::now()->subDays(7);

        $articles = NewsRaw::query()
            ->with(['source:id,name,scope,homepage_url,base_url', 'shortLink'])
            ->where(function ($query) use ($threshold): void {
                $query->where('published_at', '>=', $threshold)
                    ->orWhere(function ($inner) use ($threshold): void {
                        $inner->whereNull('published_at')
                            ->where('created_at', '>=', $threshold);
                    });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit(120)
            ->get();

        $manualPosts = NewsPost::query()
            ->published()
            ->where('published_at', '>=', $threshold)
            ->orderByDesc('is_highlighted')
            ->orderByDesc('published_at')
            ->limit(40)
            ->get();

        $sourcesList = NewsSource::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['name', 'base_url', 'homepage_url']);

        $today = Carbon::now('Europe/Bucharest')->startOfDay();
        $nowBucharest = Carbon::now('Europe/Bucharest');

        /** @var Collection<int, array<string, mixed>> $externalNews */
        $externalNews = $articles->map(function (NewsRaw $article) use ($today, $nowBucharest): array {
            $timestamp = $article->published_at ?? $article->created_at;
            $localized = $timestamp?->copy()->setTimezone('Europe/Bucharest');
            $meta = $article->meta ?? [];
            $summaryRaw = $article->body_text ?? data_get($meta, 'summary');
            $summary = is_string($summaryRaw)
                ? Str::limit(trim(strip_tags($summaryRaw)), 400)
                : null;
            $publishedDate = null;

            if ($localized !== null && ! $localized->isSameDay($today)) {
                $publishedDate = mb_strtolower($localized->locale('ro')->isoFormat('MMM D'));
            }

            $shortLink = $this->shortLinks->getOrCreateForArticle($article);

            $sortTimestamp = $timestamp?->getTimestamp() ?? $article->created_at?->getTimestamp() ?? 0;

            return [
                'id' => $article->id,
                'title' => $article->title,
                'source' => $article->source_name ?? optional($article->source)->name,
                'scope' => optional($article->source)->scope ?? 'local',
                'category' => data_get($article->meta, 'category'),
                'short_url' => route('short-links.redirect', $shortLink->code),
                'source_url' => $article->source_url,
                'redirect_count' => $shortLink->click_count ?? 0,
                'published_time' => $localized?->format('H:i') ?? '—',
                'published_label' => $localized?->diffForHumans($nowBucharest, parts: 2, short: true) ?? 'recent',
                'published_date' => $publishedDate,
                'summary' => $summary,
                'is_custom' => false,
                'is_external' => true,
                'is_highlighted' => false,
                'sort_timestamp' => $sortTimestamp,
            ];
        });

        /** @var Collection<int, array<string, mixed>> $manualNews */
        $manualNews = $manualPosts->map(function (NewsPost $post) use ($today, $nowBucharest): array {
            $timestamp = $post->published_at;
            $localized = $timestamp->copy()->setTimezone('Europe/Bucharest');
            $publishedDate = null;

            if (! $localized->isSameDay($today)) {
                $publishedDate = mb_strtolower($localized->locale('ro')->isoFormat('MMM D'));
            }

            $summarySource = $post->summary ?? $post->body_text ?? $post->body_html ?? '';
            $summary = $summarySource !== ''
                ? Str::limit(trim(strip_tags($summarySource)), 400)
                : null;

            $detailUrl = route('news-posts.show', [
                'category' => $post->category_slug,
                'slug' => $post->slug,
            ]);

            $sortTimestamp = $timestamp->getTimestamp();

            return [
                'id' => $post->id,
                'title' => $post->title ?? null,
                'source' => 'craiova.ro',
                'scope' => 'local',
                'category' => $post->category_label ?? $post->category_slug,
                'short_url' => $detailUrl,
                'source_url' => $detailUrl,
                'redirect_count' => 0,
                'published_time' => $localized->format('H:i'),
                'published_label' => $localized->diffForHumans($nowBucharest, parts: 2, short: true),
                'published_date' => $publishedDate,
                'summary' => $summary,
                'is_custom' => true,
                'is_external' => false,
                'is_highlighted' => (bool) $post->is_highlighted,
                'sort_timestamp' => $sortTimestamp,
            ];
        });

        /** @var Collection<int, array<string, mixed>> $sidebarHighlights */
        $sidebarHighlights = $manualNews
            ->sortByDesc('sort_timestamp')
            ->values()
            ->map(
                /** @return array<string, mixed> */
                static function (array $item): array {
                    $publishedLabel = $item['published_label'] !== '' ? $item['published_label'] : 'recent';
                    $publishedTime = $item['published_time'] !== '' ? $item['published_time'] : null;
                    $title = $item['title'] ?? (string) $item['id'];
                    $category = $item['category'] !== null && $item['category'] !== '' ? $item['category'] : null;

                    return [
                        'id' => $item['id'],
                        'title' => $title,
                        'url' => $item['short_url'],
                        'category' => $category,
                        'published_time' => $publishedTime,
                        'published_label' => $publishedLabel,
                        'is_highlighted' => $item['is_highlighted'],
                    ];
                }
            )
            ->take(6)
            ->values();

        /** @var Collection<int, array<string, mixed>> $currentNews */
        $currentNews = $manualNews
            ->concat($externalNews)
            ->sortByDesc('sort_timestamp')
            ->values()
            ->map(
                /** @return array<string, mixed> */
                static function (array $item): array {
                    $source = $item['source'] !== null && $item['source'] !== '' ? $item['source'] : null;
                    $scope = $item['scope'] !== '' ? $item['scope'] : 'local';
                    $category = $item['category'] !== null && $item['category'] !== '' ? $item['category'] : null;
                    $publishedLabel = $item['published_label'] !== '' ? $item['published_label'] : 'recent';
                    $publishedTime = $item['published_time'] !== '' ? $item['published_time'] : null;

                    return [
                        'id' => (int) $item['id'],
                        'title' => $item['title'] ?? null,
                        'source' => $source,
                        'scope' => $scope,
                        'category' => $category,
                        'short_url' => $item['short_url'],
                        'source_url' => $item['source_url'],
                        'redirect_count' => (int) $item['redirect_count'],
                        'published_time' => $publishedTime,
                        'published_label' => $publishedLabel,
                        'published_date' => $item['published_date'] ?? null,
                        'summary' => $item['summary'] ?? null,
                        'is_custom' => (bool) $item['is_custom'],
                        'is_external' => (bool) $item['is_external'],
                        'is_highlighted' => (bool) $item['is_highlighted'],
                    ];
                }
            );

        $topics = $this->buildTopics($articles);

        $sources = $sourcesList->map(fn (NewsSource $source): array => [
            'name' => $source->name,
            'url' => $source->homepage_url ?? $this->guessHomepageUrl($source->base_url),
        ]);

        return [
            'currentNews' => $currentNews,
            'sidebarHighlights' => $sidebarHighlights,
            'topics' => $topics,
            'sourcesList' => $sources,
            'refreshedAt' => Carbon::now('Europe/Bucharest'),
        ];
    }

    /**
     * @param  Collection<int, NewsRaw>  $articles
     * @return Collection<int, array<string, mixed>>
     */
    protected function buildTopics(Collection $articles): Collection
    {
        $groups = [];

        foreach ($articles as $article) {
            if ($article->title === null) {
                continue;
            }

            $key = $this->topicKey($article->title);

            if ($key === null) {
                continue;
            }

            $groups[$key]['articles'][] = $article;
        }

        $topics = collect($groups)
            ->map(
                /** @return array<string, mixed> */
                function (array $group): array {
                    /** @var Collection<int, NewsRaw> $items */
                    $items = collect($group['articles']);

                    $items = $items->sortByDesc(function (NewsRaw $article): int {
                        $timestamp = $article->published_at ?? $article->created_at;

                        return $timestamp?->getTimestamp() ?? 0;
                    });

                    /** @var NewsRaw|null $lead */
                    $lead = $items->first();

                    if ($lead === null) {
                        return [
                            'title' => null,
                            'source' => null,
                            'scope' => 'local',
                            'category' => null,
                            'short_url' => '',
                            'published_time' => null,
                            'similar_count' => 0,
                        ];
                    }

                    $timestamp = $lead->published_at ?? $lead->created_at;
                    $localized = $timestamp?->copy()->setTimezone('Europe/Bucharest');
                    $publishedTime = $localized?->format('d/m H:i');
                    $publishedTime = is_string($publishedTime) ? $publishedTime : null;

                    $sourceName = $lead->source_name ?? optional($lead->source)->name;

                    if (! is_string($sourceName) || $sourceName === '') {
                        $sourceName = null;
                    }

                    $scope = optional($lead->source)->scope;

                    if (! is_string($scope) || $scope === '') {
                        $scope = 'local';
                    }

                    $category = data_get($lead->meta, 'category');

                    if (! is_string($category) || $category === '') {
                        $category = null;
                    }

                    return [
                        'title' => $lead->title,
                        'source' => $sourceName,
                        'scope' => $scope,
                        'category' => $category,
                        'short_url' => route('short-links.redirect', $this->shortLinks->getOrCreateForArticle($lead)->code),
                        'published_time' => $publishedTime,
                        'similar_count' => max(0, $items->count() - 1),
                    ];
                }
            )
            ->filter(fn (array $topic): bool => $topic['title'] !== null)
            ->sortByDesc(fn (array $topic): int => $topic['similar_count'])
            ->values()
            ->take(12);

        return $topics;
    }

    protected function topicKey(string $title): ?string
    {
        $normalized = Str::ascii(Str::lower($title));
        $normalized = preg_replace('/[^a-z0-9\s]/u', ' ', $normalized);

        if (! is_string($normalized)) {
            return null;
        }

        $tokens = collect(explode(' ', $normalized))
            ->filter(fn (string $token): bool => $token !== '')
            ->reject(fn (string $token): bool => in_array($token, $this->stopWords(), true))
            ->values();

        if ($tokens->isEmpty()) {
            return null;
        }

        return $tokens->take(6)->implode('-');
    }

    /**
     * @return array<int, string>
     */
    protected function stopWords(): array
    {
        return [
            'a', 'ai', 'al', 'ale', 'care', 'ce', 'cu', 'de', 'din', 'dupa', 'este', 'fi', 'iar', 'in', 'la',
            'lui', 'luna', 'mai', 'ne', 'noi', 'nu', 'o', 'pe', 'pentru', 'prin', 're', 'sa', 'si', 'sunt',
            'un', 'una', 'unui', 'unor', 'va', 'va', 'vor', 'fost', 'cum', 'despre', 'intr', 'intre', 'cum',
        ];
    }

    protected function guessHomepageUrl(string $baseUrl): string
    {
        $parsed = parse_url($baseUrl);

        if (! $parsed || ! isset($parsed['host'])) {
            return $baseUrl;
        }

        $scheme = $parsed['scheme'] ?? 'https';

        return $scheme.'://'.$parsed['host'].'/';
    }
}
