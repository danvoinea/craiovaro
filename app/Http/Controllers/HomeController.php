<?php

namespace App\Http\Controllers;

use App\Models\NewsRaw;
use App\Services\Links\ShortLinkService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected ShortLinkService $shortLinks,
    ) {}

    public function show(): View
    {
        $payload = Cache::remember('home:payload', now()->addSeconds(60), function () {
            $articles = NewsRaw::query()
                ->with(['source:id,name', 'shortLink'])
                ->orderByDesc('published_at')
                ->orderByDesc('created_at')
                ->limit(120)
                ->get();

            $today = Carbon::now('Europe/Bucharest')->startOfDay();

            $currentNews = $articles->map(function (NewsRaw $article) use ($today): array {
                $timestamp = $article->published_at ?? $article->created_at;
                $localized = $timestamp?->copy()->setTimezone('Europe/Bucharest');
                $meta = $article->meta ?? [];
                $summaryRaw = $article->body_text ?? ($meta['summary'] ?? null);
                $summary = is_string($summaryRaw)
                    ? Str::limit(trim(strip_tags($summaryRaw)), 400)
                    : null;
                $publishedDate = null;

                if ($localized !== null && ! $localized->isSameDay($today)) {
                    $publishedDate = mb_strtolower($localized->locale('ro')->isoFormat('MMM D'));
                }

                $shortLink = $this->shortLinks->getOrCreateForArticle($article);

                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'source' => $article->source_name ?? $article->source?->name,
                    'short_url' => route('short-links.redirect', $shortLink->code),
                    'source_url' => $article->source_url,
                    'published_time' => $localized?->format('H:i'),
                    'published_label' => $localized?->diffForHumans(now('Europe/Bucharest'), parts: 2, short: true) ?? 'recent',
                    'published_date' => $publishedDate,
                    'summary' => $summary,
                ];
            });

            $topics = $this->buildTopics($articles);

            return [
                'currentNews' => $currentNews,
                'topics' => $topics,
                'refreshedAt' => Carbon::now('Europe/Bucharest'),
            ];
        });

        return view('home', $payload);
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

        return collect($groups)
            ->map(function (array $group): array {
                /** @var Collection<int, NewsRaw> $items */
                $items = collect($group['articles']);

                $items = $items->sortByDesc(function (NewsRaw $article): int {
                    $timestamp = $article->published_at ?? $article->created_at;

                    return $timestamp?->timestamp ?? 0;
                });

                /** @var NewsRaw|null $lead */
                $lead = $items->first();

                if ($lead === null) {
                    return [
                        'title' => null,
                        'source' => null,
                        'source_url' => null,
                        'published_time' => null,
                        'similar_count' => 0,
                    ];
                }

                $timestamp = $lead->published_at ?? $lead->created_at;
                $localized = $timestamp?->copy()->setTimezone('Europe/Bucharest');

                return [
                    'title' => $lead->title,
                    'source' => $lead->source_name ?? $lead->source?->name,
                    'short_url' => route('short-links.redirect', $this->shortLinks->getOrCreateForArticle($lead)->code),
                    'published_time' => $localized?->format('d/m H:i'),
                    'similar_count' => max(0, $items->count() - 1),
                ];
            })
            ->filter(fn (array $topic): bool => $topic['title'] !== null)
            ->sortByDesc(fn (array $topic): int => $topic['similar_count'])
            ->values()
            ->take(12);
    }

    protected function topicKey(string $title): ?string
    {
        $normalized = Str::ascii(Str::lower($title));
        $normalized = preg_replace('/[^a-z0-9\s]/u', ' ', $normalized ?? '');

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
}
