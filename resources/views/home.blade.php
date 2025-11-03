<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>craiova.ro · Știri din Craiova</title>
    <style>
        :root {
            color-scheme: light;
            --brand-green: #4caf50;
            --brand-gray: #e7ebed;
            --text-muted: #5f6b7b;
            --text-dark: #1a1d21;
            --border-color: #d9dde3;
            --bg-panel: #ffffff;
            --bg-page: #f4f6f8;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--bg-page);
            color: var(--text-dark);
            line-height: 1.5;
        }

        body {
            padding: 32px clamp(16px, 3vw, 48px);
        }

        .page {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            gap: 32px;
        }

        .hero {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .hero h1 {
            margin: 0;
            font-size: clamp(28px, 4vw, 40px);
            font-weight: 700;
        }

        .hero p {
            margin: 0;
            color: var(--text-muted);
            font-size: 15px;
        }

        .content {
            display: grid;
            gap: 24px;
        }

        @media (min-width: 960px) {
            .content {
                grid-template-columns: minmax(0, 2.3fr) minmax(280px, 1fr);
                align-items: start;
            }
        }

        .panel {
            background: var(--bg-panel);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 24px rgba(20, 33, 61, 0.08);
        }

        .panel-header {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .tabs {
            display: inline-flex;
            gap: 12px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
        }

        .tab {
            padding: 6px 12px;
            border-radius: 999px;
            background: transparent;
        }

        .tab.is-active {
            background: var(--brand-green);
            color: #fff;
        }

        .panel-header small {
            font-size: 13px;
            color: var(--text-muted);
        }

        .news-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        details.news-card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 14px 18px;
            background: #fff;
            transition: border 0.2s ease, box-shadow 0.2s ease;
        }

        details.news-card[open] {
            border-color: var(--brand-green);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.12);
        }

        details.news-card summary {
            list-style: none;
            cursor: pointer;
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        details.news-card summary::marker,
        details.news-card summary::-webkit-details-marker {
            display: none;
        }

        .news-time {
            min-width: 58px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            color: var(--text-muted);
        }

        .news-time-value {
            font-size: 13px;
            font-weight: 600;
            line-height: 1.2;
        }

        .news-date {
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.02em;
            text-transform: lowercase;
        }

        .news-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .news-title a {
            color: inherit;
            text-decoration: none;
        }

        .news-title a:hover {
            text-decoration: underline;
        }

        .news-source {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 4px;
            font-size: 13px;
            color: var(--brand-green);
            font-weight: 600;
        }

        .news-meta {
            margin-top: 12px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .news-summary {
            margin-top: 14px;
            font-size: 14px;
            color: #2a2f38;
        }

        .news-actions {
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .news-link {
            font-weight: 600;
            font-size: 14px;
            color: var(--brand-green);
            text-decoration: none;
        }

        .news-link:hover {
            text-decoration: underline;
        }

        .topics {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .topics h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
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

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(76, 175, 80, 0.14);
            font-weight: 600;
            color: var(--brand-green);
        }

        @media (max-width: 640px) {
            details.news-card {
                padding: 12px 14px;
            }

            .news-title {
                font-size: 15px;
            }

            .news-summary {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <header class="hero">
            <h1>craiova.ro</h1>
            <p>Actualizăm automat știrile locale din Craiova și Dolj. Ultima actualizare: {{ $refreshedAt->diffForHumans() }}.</p>
        </header>

        <div class="content">
            <section class="panel">
                <div class="panel-header">
                    <div class="tabs">
                        <span class="tab is-active">Știri curente</span>
                        <span class="tab">Subiectele zilei</span>
                    </div>
                    <small>Flux live · {{ $refreshedAt->timezone('Europe/Bucharest')->format('d.m H:i') }}</small>
                </div>

                <div class="news-list">
                    @forelse ($currentNews as $item)
                        <details class="news-card">
                            <summary>
                                <span class="news-time">
                                    <span class="news-time-value">{{ $item['published_time'] ?? '—' }}</span>
                                    @if ($item['published_date'])
                                        <span class="news-date">{{ $item['published_date'] }}</span>
                                    @endif
                                </span>
                                <span>
                                    <span class="news-title">
                                        @if ($item['short_url'])
                                            <a href="{{ $item['short_url'] }}" target="_blank" rel="noopener">{{ $item['title'] }}</a>
                                        @else
                                            {{ $item['title'] }}
                                        @endif
                                    </span>
                                    <div class="news-source">
                                        <span>{{ $item['source'] ?? 'Sursă necunoscută' }}</span>
                                    </div>
                                </span>
                            </summary>

                            @if ($item['summary'])
                                <div class="news-summary">{{ $item['summary'] }}</div>
                            @endif

                            <div class="news-meta">
                                <span>{{ $item['published_label'] }}</span>
                                @if ($item['short_url'])
                                    <a class="news-link" href="{{ $item['short_url'] }}" target="_blank" rel="noopener">Deschide articolul original</a>
                                @endif
                            </div>
                        </details>
                    @empty
                        <p>Nu avem știri disponibile în acest moment. Reveniți mai târziu.</p>
                    @endforelse
                </div>
            </section>

            <aside class="panel topics">
                <h2>Subiectele zilei</h2>
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
                                    <span>{{ $topic['source'] }}</span>
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
            </aside>
        </div>
    </div>
</body>
</html>
