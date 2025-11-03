<?php

namespace App\Services\News;

use App\Models\NewsRaw;
use App\Models\NewsSource;
use App\Models\NewsSourceLog;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Throwable;

class NewsScraperService
{
    protected ?CssSelectorConverter $cssSelectorConverter = null;

    /**
     * @return array<string, mixed>
     */
    public function fetch(NewsSource $source): array
    {
        $startedAt = microtime(true);
        $status = 'success';
        $message = null;
        $summary = [
            'processed' => 0,
            'created' => 0,
            'duplicates' => 0,
            'filtered' => 0,
            'errors' => 0,
        ];

        try {
            $items = $this->collectItems($source);
            $summary['processed'] = count($items);

            foreach ($items as $item) {
                $result = $this->storeArticleFromItem($source, $item);

                match ($result) {
                    'created' => $summary['created']++,
                    'duplicate' => $summary['duplicates']++,
                    'filtered' => $summary['filtered']++,
                    'error' => $summary['errors']++,
                    default => null,
                };
            }

            $message = sprintf(
                'Processed %d items: %d new, %d duplicates, %d filtered, %d errors.',
                $summary['processed'],
                $summary['created'],
                $summary['duplicates'],
                $summary['filtered'],
                $summary['errors']
            );
        } catch (Throwable $exception) {
            report($exception);
            $status = 'error';
            $message = $exception->getMessage();
        }

        $duration = (int) ((microtime(true) - $startedAt) * 1000);
        $fetchedAt = Carbon::now();

        $source->markFetched($fetchedAt, $status);

        NewsSourceLog::query()->create([
            'news_source_id' => $source->id,
            'status' => $status,
            'message' => $message,
            'ran_at' => $fetchedAt,
            'duration_ms' => $duration,
            'context' => $summary,
        ]);

        return [
            'status' => $status,
            'message' => $message,
            'duration_ms' => $duration,
            'summary' => $summary,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function collectItems(NewsSource $source): array
    {
        return match ($source->source_type) {
            'rss' => $this->collectFromRss($source),
            'sitemap' => $this->collectFromSitemap($source),
            default => $this->collectFromHtmlListing($source),
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function collectFromRss(NewsSource $source): array
    {
        $body = $this->downloadContent($source->base_url);

        $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            throw new RuntimeException('Unable to parse RSS response.');
        }

        $items = $xml->channel?->item ?? $xml->item;

        if (! $items) {
            return [];
        }

        $results = [];

        foreach ($items as $item) {
            $url = (string) ($item->link ?? '');

            if ($url === '') {
                continue;
            }

            $results[] = [
                'url' => $url,
                'title' => $item->title ? (string) $item->title : null,
                'summary' => $this->extractRssSummary($item),
                'published_at' => $this->parseDate($item->pubDate ?? null),
                'cover_image_url' => $this->extractRssCoverImage($item, $url),
            ];

            if (count($results) >= 50) {
                break;
            }
        }

        return $results;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function collectFromSitemap(NewsSource $source): array
    {
        $body = $this->downloadContent($source->base_url);
        $xml = simplexml_load_string($body);

        if ($xml === false) {
            throw new RuntimeException('Unable to parse sitemap response.');
        }

        $urls = [];

        if ($xml->url) {
            foreach ($xml->url as $node) {
                $url = (string) ($node->loc ?? '');

                if ($url === '') {
                    continue;
                }

                $urls[] = [
                    'url' => $url,
                    'title' => null,
                    'summary' => null,
                    'published_at' => $this->parseDate($node->lastmod ?? null),
                    'cover_image_url' => null,
                ];

                if (count($urls) >= 100) {
                    break;
                }
            }

            return $urls;
        }

        if ($xml->sitemap) {
            foreach ($xml->sitemap as $node) {
                $loc = (string) ($node->loc ?? '');

                if ($loc === '') {
                    continue;
                }

                try {
                    $nestedBody = $this->downloadContent($loc);
                } catch (Throwable) {
                    continue;
                }

                $nestedXml = simplexml_load_string($nestedBody);

                if ($nestedXml === false || ! $nestedXml->url) {
                    continue;
                }

                foreach ($nestedXml->url as $urlNode) {
                    $url = (string) ($urlNode->loc ?? '');

                    if ($url === '') {
                        continue;
                    }

                    $urls[] = [
                        'url' => $url,
                        'title' => null,
                        'summary' => null,
                        'published_at' => $this->parseDate($urlNode->lastmod ?? null),
                        'cover_image_url' => null,
                    ];

                    if (count($urls) >= 100) {
                        break 2;
                    }
                }
            }
        }

        return $urls;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function collectFromHtmlListing(NewsSource $source): array
    {
        $body = $this->downloadContent($source->base_url);
        $document = $this->createDomDocument($body);

        if (blank($source->link_selector)) {
            throw new RuntimeException('Link selector is required for HTML sources.');
        }

        $links = $this->findNodes($document, $source->link_selector, $source->selector_type ?? 'css');

        $results = [];

        foreach ($links as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $href = $node->getAttribute('href');

            $url = $this->resolveUrl($href, $source->base_url);

            if ($url === null) {
                continue;
            }

            $results[] = [
                'url' => $url,
                'title' => trim($node->textContent ?? ''),
                'summary' => null,
                'published_at' => null,
                'cover_image_url' => null,
            ];

            if (count($results) >= 40) {
                break;
            }
        }

        return $results;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function storeArticleFromItem(NewsSource $source, array $item): string
    {
        $url = $item['url'] ?? null;

        if ($url === null) {
            return 'error';
        }

        $hash = hash('sha256', $url);

        if (NewsRaw::query()->where('url_hash', $hash)->exists()) {
            return 'duplicate';
        }

        try {
            $payload = $this->buildArticlePayload($source, $item);
        } catch (Throwable $exception) {
            report($exception);

            return 'error';
        }

        if ($payload === null) {
            return 'filtered';
        }

        $payload['url_hash'] = $hash;
        $payload['news_source_id'] = $source->id;
        $payload['source_name'] = $source->name;
        $payload['meta']['source_type'] = $source->source_type;

        NewsRaw::query()->create($payload);

        return 'created';
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|null
     */
    protected function buildArticlePayload(NewsSource $source, array $item): ?array
    {
        $title = $item['title'] ?? null;
        $coverImage = $item['cover_image_url'] ?? null;
        $summary = $item['summary'] ?? null;
        $publishedAt = $item['published_at'] instanceof Carbon ? $item['published_at'] : null;

        $needsPageFetch = $this->shouldFetchPage($source, $summary);
        $articleHtml = null;

        if ($needsPageFetch) {
            $articleHtml = $this->downloadContent($item['url']);
            $parsed = $this->parseArticleDocument($source, $articleHtml, $item['url']);

            $title = $parsed['title'] ?? $title;
            $coverImage = $parsed['cover_image_url'] ?? $coverImage;
            $bodyHtml = $parsed['body_html'] ?? null;
            $bodyText = $parsed['body_text'] ?? null;
            $publishedAt ??= $parsed['published_at'] ?? null;
        } else {
            $bodyHtml = $summary;
            $bodyText = $summary ? $this->toPlainText($summary) : null;
        }

        if (! isset($bodyHtml) && $articleHtml !== null) {
            $parsed = $this->parseArticleDocument($source, $articleHtml, $item['url']);
            $bodyHtml = $parsed['body_html'] ?? null;
            $bodyText = $parsed['body_text'] ?? null;
            $coverImage ??= $parsed['cover_image_url'] ?? null;
            $title ??= $parsed['title'] ?? null;
            $publishedAt ??= $parsed['published_at'] ?? null;
        }

        $title ??= 'Untitled article';

        if ($bodyHtml === null && $bodyText === null) {
            $bodyText = $summary !== null ? $this->toPlainText($summary) : null;
        }

        if ($bodyHtml === null && $bodyText === null) {
            return null;
        }

        if ($bodyHtml === null && $bodyText !== null) {
            $bodyHtml = '<p>'.$bodyText.'</p>';
        }

        if ($this->shouldFilterByKeywords($source, $title, $bodyText ?? '')) {
            return null;
        }

        return [
            'title' => $title,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText ?? $this->toPlainText($bodyHtml),
            'published_at' => $publishedAt,
            'source_url' => $item['url'],
            'cover_image_url' => $coverImage,
            'meta' => [
                'summary' => $summary,
            ],
        ];
    }

    protected function shouldFetchPage(NewsSource $source, ?string $summary): bool
    {
        if ($source->source_type !== 'rss') {
            return true;
        }

        if ($source->title_selector || $source->body_selector || $source->image_selector || $source->date_selector) {
            return true;
        }

        return $summary === null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseArticleDocument(NewsSource $source, string $html, string $currentUrl): array
    {
        $document = $this->createDomDocument($html);
        $selectorType = $source->selector_type ?? 'css';

        $title = $this->extractNodeValueFromDocument($document, $source->title_selector, $selectorType, 'text');
        $bodyHtml = $this->extractNodeValueFromDocument($document, $source->body_selector, $selectorType, 'html');
        $bodyText = $bodyHtml !== null
            ? $this->toPlainText($bodyHtml)
            : $this->extractNodeValueFromDocument($document, $source->body_selector, $selectorType, 'text');
        $coverImage = $this->extractNodeValueFromDocument($document, $source->image_selector, $selectorType, 'attr');

        if ($coverImage !== null) {
            $coverImage = $this->resolveUrl($coverImage, $currentUrl);
        }

        $dateRaw = $this->extractNodeValueFromDocument($document, $source->date_selector, $selectorType, 'text');
        $publishedAt = $this->parseDate($dateRaw);

        return [
            'title' => $title,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
            'cover_image_url' => $coverImage,
            'published_at' => $publishedAt,
        ];
    }

    protected function downloadContent(string $url): string
    {
        try {
            $response = Http::withUserAgent('craiova.ro bot/1.0 (+https://craiova.ro)')
                ->accept('*/*')
                ->timeout(20)
                ->retry(2, 1000)
                ->get($url)
                ->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException(sprintf('Failed to fetch %s (%s)', $url, $exception->getMessage()), previous: $exception);
        }

        return $response->body();
    }

    protected function extractRssSummary(mixed $item): ?string
    {
        $namespaces = $item->getNameSpaces(true);

        if (isset($namespaces['content'])) {
            $encoded = $item->children($namespaces['content'])->encoded ?? null;

            if ($encoded !== null) {
                return (string) $encoded;
            }
        }

        if (isset($item->description)) {
            return (string) $item->description;
        }

        return null;
    }

    protected function extractRssCoverImage(mixed $item, string $fallbackUrl): ?string
    {
        if (isset($item->enclosure) && isset($item->enclosure['url'])) {
            return (string) $item->enclosure['url'];
        }

        $summary = $this->extractRssSummary($item);

        if ($summary !== null) {
            preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $summary, $matches);

            if (isset($matches[1])) {
                return $this->resolveUrl($matches[1], $fallbackUrl);
            }
        }

        return null;
    }

    protected function parseDate(mixed $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp((int) $value);
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value);
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }

    protected function resolveUrl(string $url, string $baseUrl): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        if (Str::startsWith($url, '//')) {
            $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?: 'https';

            return $scheme.'://'.ltrim($url, '/');
        }

        if (Str::startsWith($url, '/')) {
            $parts = parse_url($baseUrl);

            if (! $parts || ! isset($parts['scheme'], $parts['host'])) {
                return null;
            }

            $port = isset($parts['port']) ? ':'.$parts['port'] : '';

            return $parts['scheme'].'://'.$parts['host'].$port.$url;
        }

        return rtrim($baseUrl, '/').'/'.ltrim($url, '/');
    }

    protected function shouldFilterByKeywords(NewsSource $source, string $title, string $bodyText): bool
    {
        $keywords = $source->keywordsList();

        if ($keywords === []) {
            return false;
        }

        $haystack = mb_strtolower($title.' '.$bodyText);

        foreach ($keywords as $keyword) {
            if ($keyword === '') {
                continue;
            }

            if (Str::contains($haystack, mb_strtolower($keyword))) {
                return false;
            }
        }

        return true;
    }

    protected function toPlainText(string $html): string
    {
        $text = strip_tags($html);

        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    protected function parseSelector(string $selector): array
    {
        $parts = explode('|', $selector, 2);
        $cleanSelector = trim($parts[0]);
        $attribute = isset($parts[1]) ? trim($parts[1]) : null;

        return [$cleanSelector, $attribute !== '' ? $attribute : null];
    }

    protected function extractNodeValueFromDocument(DOMDocument $document, ?string $selector, string $selectorType, string $mode): ?string
    {
        if ($selector === null || trim($selector) === '') {
            return null;
        }

        [$cleanSelector, $attribute] = $this->parseSelector($selector);

        $xpath = new DOMXPath($document);
        $expression = $selectorType === 'xpath'
            ? $cleanSelector
            : $this->cssConverter()->toXPath($cleanSelector);

        $nodes = $xpath->query($expression);

        if ($nodes === false || $nodes->length === 0) {
            return null;
        }

        $node = $nodes->item(0);

        if (! $node instanceof DOMElement) {
            return $mode === 'html' ? null : trim($node?->textContent ?? '');
        }

        if ($attribute !== null || $mode === 'attr') {
            $attributeName = $attribute ?? ($mode === 'attr' ? 'src' : 'content');
            $value = $node->getAttribute($attributeName);

            return $value !== '' ? trim($value) : null;
        }

        if ($mode === 'html') {
            return $this->innerHtml($node);
        }

        return trim($node->textContent ?? '');
    }

    /**
     * @return array<int, DOMElement>
     */
    protected function findNodes(DOMDocument $document, string $selector, string $selectorType): array
    {
        $xpath = new DOMXPath($document);
        $expression = $selectorType === 'xpath'
            ? $selector
            : $this->cssConverter()->toXPath($selector);

        $nodes = $xpath->query($expression);

        if ($nodes === false) {
            return [];
        }

        $results = [];

        foreach ($nodes as $node) {
            if ($node instanceof DOMElement) {
                $results[] = $node;
            }
        }

        return $results;
    }

    protected function createDomDocument(string $html): DOMDocument
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previousLibxmlUseInternalErrors = libxml_use_internal_errors(true);
        $htmlContent = '<?xml encoding="UTF-8">'.mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $document->loadHTML($htmlContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previousLibxmlUseInternalErrors);

        return $document;
    }

    protected function innerHtml(DOMElement $element): string
    {
        $html = '';

        foreach ($element->childNodes as $child) {
            $html .= $element->ownerDocument?->saveHTML($child) ?? '';
        }

        return trim($html);
    }

    protected function cssConverter(): CssSelectorConverter
    {
        return $this->cssSelectorConverter ??= new CssSelectorConverter;
    }
}
