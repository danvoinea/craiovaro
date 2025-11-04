<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $post->title }} · craiova.ro</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="{{ $post->title }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($post->hero_image_url)
        <meta property="og:image" content="{{ $post->hero_image_url }}">
    @endif
    <style>
        :root {
            color-scheme: light;
            --brand-green: #4caf50;
            --text-muted: #5f6b7b;
            --text-dark: #1a1d21;
            --border-color: #d9dde3;
            --bg-page: #f4f6f8;
            --bg-panel: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg-page);
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
            padding: 32px clamp(16px, 3vw, 48px);
        }

        .page {
            max-width: 1040px;
            margin: 0 auto;
            display: grid;
            gap: 32px;
        }

        header.hero {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .hero-title {
            margin: 0;
            font-size: clamp(28px, 4vw, 42px);
            font-weight: 700;
        }

        .hero-meta {
            font-size: 14px;
            color: var(--text-muted);
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .category-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(76, 175, 80, 0.14);
            color: var(--brand-green);
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            font-size: 12px;
        }

        .article {
            background: var(--bg-panel);
            border-radius: 16px;
            padding: clamp(24px, 4vw, 40px);
            border: 1px solid var(--border-color);
            box-shadow: 0 12px 32px rgba(20, 33, 61, 0.08);
        }

        .article-summary {
            font-size: 18px;
            font-weight: 500;
            color: var(--text-dark);
            margin: 16px 0 24px;
        }

        .article-body {
            font-size: 16px;
            color: #2a2f38;
        }

        .article-body h2,
        .article-body h3 {
            color: var(--text-dark);
            margin-top: 32px;
        }

        .article-body p {
            margin: 16px 0;
        }

        .article-body a {
            color: var(--brand-green);
        }

        .hero-actions {
            display: flex;
            gap: 16px;
            margin-top: 24px;
        }

        .hero-actions a {
            text-decoration: none;
            font-weight: 600;
            color: var(--brand-green);
        }

        .related {
            display: grid;
            gap: 16px;
        }

        .related h2 {
            margin: 0;
            font-size: 20px;
        }

        .related-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 12px;
        }

        .related-list a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 600;
        }

        .related-list a:hover {
            text-decoration: underline;
        }

        .related-meta {
            font-size: 12px;
            color: var(--text-muted);
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .hero-logo {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 700;
            font-size: 18px;
        }

        @media (min-width: 960px) {
            .page {
                grid-template-columns: minmax(0, 2.5fr) minmax(240px, 1fr);
                align-items: start;
            }

            header.hero {
                grid-column: 1 / -1;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <header class="hero">
            <a class="hero-logo" href="{{ route('home.show') }}">craiova.ro</a>
            <span class="category-pill">{{ $categoryName }}</span>
            <h1 class="hero-title">{{ $post->title }}</h1>
            <div class="hero-meta">
                <span>Publicat {{ $post->published_at?->timezone('Europe/Bucharest')->translatedFormat('d F Y, H:i') }}</span>
                <span>Autor: craiova.ro</span>
            </div>
            <div class="hero-actions">
                <a href="{{ route('home.show') }}">Înapoi la flux</a>
                <a href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener">Distribuie</a>
            </div>
        </header>

        <article class="article">
            @if ($post->summary)
                <div class="article-summary">{{ $post->summary }}</div>
            @endif

            <div class="article-body">
                @if ($post->body_html)
                    {!! $post->body_html !!}
                @elseif ($post->body_text)
                    <p>{{ $post->body_text }}</p>
                @else
                    <p>Acest articol nu are încă detalii suplimentare.</p>
                @endif
            </div>
        </article>

        <aside class="related">
            <h2>Alte știri din {{ $categoryName }}</h2>
            <ul class="related-list">
                @forelse ($relatedPosts as $related)
                    <li>
                        <a href="{{ route('news-posts.show', ['category' => $related->category_slug, 'slug' => $related->slug]) }}">{{ $related->title }}</a>
                        <div class="related-meta">
                            <span>{{ $related->published_at?->timezone('Europe/Bucharest')->format('d.m H:i') }}</span>
                        </div>
                    </li>
                @empty
                    <li>Nu există alte știri similare momentan.</li>
                @endforelse
            </ul>
        </aside>
    </div>
</body>
</html>
