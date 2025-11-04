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

        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .sidebar-section {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .sidebar-section + .sidebar-section {
            padding-top: 24px;
            border-top: 1px solid var(--border-color);
        }

        .sidebar-highlights,
        .sidebar-latest {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .sidebar-highlight-item,
        .sidebar-latest-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sidebar-highlight-item a,
        .sidebar-latest-item a {
            font-weight: 600;
            color: var(--text-dark);
            text-decoration: none;
        }

        .sidebar-highlight-item a:hover,
        .sidebar-latest-item a:hover {
            text-decoration: underline;
        }

        .sidebar-highlight-meta,
        .sidebar-latest-meta {
            font-size: 12px;
            color: var(--text-muted);
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .sidebar-highlight-meta .news-category,
        .sidebar-latest-meta .news-category {
            font-size: 11px;
        }

        .topics-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .topic-item {
            display: grid;
            grid-template-columns: 28px minmax(0, 1fr);
            gap: 12px;
            align-items: start;
        }

        .topic-rank {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(76, 175, 80, 0.12);
            color: var(--brand-green);
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 14px;
        }

        .topic-content {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .topic-content a {
            font-weight: 600;
            color: var(--text-dark);
            text-decoration: none;
        }

        .topic-content a:hover {
            text-decoration: underline;
        }

        .topic-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .topics-links {
            gap: 12px;
        }

        .topics-links ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .topics-links a {
            color: var(--text-dark);
            text-decoration: none;
        }

        .topics-links a:hover {
            text-decoration: underline;
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

        <aside class="sidebar">
            @if ($sidebarHighlights->isNotEmpty())
                <div class="sidebar-section">
                    <h2>Știri craiova.ro</h2>
                    <ul class="sidebar-highlights">
                        @foreach ($sidebarHighlights as $highlight)
                            <li class="sidebar-highlight-item">
                                <a href="{{ $highlight['url'] }}">{{ $highlight['title'] }}</a>
                                <div class="sidebar-highlight-meta">
                                    @if ($highlight['category'])
                                        <span class="news-category">{{ $highlight['category'] }}</span>
                                    @endif
                                    @if ($highlight['published_time'])
                                        <span>{{ $highlight['published_time'] }}</span>
                                    @endif
                                    @if ($highlight['published_label'])
                                        <span>{{ $highlight['published_label'] }}</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($latestNews->isNotEmpty())
                <div class="sidebar-section">
                    <h2>Ultimele 10 știri</h2>
                    <ul class="sidebar-latest">
                        @foreach ($latestNews as $item)
                            <li class="sidebar-latest-item">
                                <a href="{{ $item['short_url'] }}" target="_blank" rel="noopener">{{ $item['title'] }}</a>
                                <div class="sidebar-latest-meta">
                                    @if ($item['source'])
                                        <span>{{ $item['source'] }}</span>
                                    @endif
                                    @if ($item['published_time'])
                                        <span>{{ $item['published_time'] }}</span>
                                    @endif
                                    @if ($item['published_label'])
                                        <span>{{ $item['published_label'] }}</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="sidebar-section">
                <h2>Știrile zilei</h2>
                <ol class="topics-list">
                    @forelse ($topics as $index => $topic)
                        <li class="topic-item">
                            <span class="topic-rank">{{ $index + 1 }}</span>
                            <div class="topic-content">
                                @if ($topic['short_url'])
                                    <a href="{{ $topic['short_url'] }}" target="_blank" rel="noopener">{{ $topic['title'] }}</a>
                                @else
                                    {{ $topic['title'] }}
                                @endif
                                <div class="topic-meta">
                                    @if ($topic['source'])
                                        <span>{{ $topic['source'] }}</span>
                                    @endif
                                    @if ($topic['category'])
                                        <span class="news-category">{{ $topic['category'] }}</span>
                                    @endif
                                    @if ($topic['published_time'])
                                        <span>{{ $topic['published_time'] }}</span>
                                    @endif
                                    @if ($topic['similar_count'] > 0)
                                        <span class="badge">{{ $topic['similar_count'] }} știri similare</span>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @empty
                        <li>Încă nu am grupele de subiecte pentru astăzi.</li>
                    @endforelse
                </ol>
            </div>

            <div class="sidebar-section topics-links">
                <h3>Surse indexate</h3>
                <ul>
                    @foreach ($sourcesList as $source)
                        <li><a href="{{ $source['url'] }}" target="_blank" rel="noopener">{{ $source['name'] }}</a></li>
                    @endforeach
                </ul>
            </div>
        </aside>
    </div>
</body>
</html>
