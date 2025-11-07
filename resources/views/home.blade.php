@extends('layouts.app')

@section('title', 'craiova.ro · Știri din Craiova')

@section('content')
    <div class="page">
        <div class="content">
            <section class="panel">
                <div class="panel-header">
                    <div class="tabs">
                        <span class="tab is-active">Știri curente</span>
                    </div>
                    <small>Flux live · {{ $refreshedAt->timezone('Europe/Bucharest')->format('d.m H:i') }}</small>
                </div>

                <div class="news-list">
                    @forelse ($currentNews as $item)
                        <details class="news-card{{ $item['is_highlighted'] ? ' is-highlighted' : '' }}">
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
                                                <a href="{{ $item['short_url'] }}" @if ($item['is_external']) target="_blank" rel="noopener" @endif>{{ $item['title'] }}</a>
                                            @else
                                                {{ $item['title'] }}
                                            @endif
                                        </span>
                                    <div class="news-source">
                                        <span>{{ $item['source'] ?? 'Sursă necunoscută' }}</span>
                                        @if ($item['category'])
                                            <span class="news-category">{{ $item['category'] }}</span>
                                        @endif
                                        @if ($item['is_custom'])
                                            <span class="curated-badge">Selectat</span>
                                        @endif
                                    </div>
                                </span>
                            </summary>

                            @if ($item['summary'])
                                <div class="news-summary">{{ $item['summary'] }}</div>
                            @endif

                            <div class="news-meta">
                                <span>{{ $item['published_label'] }}</span>
                                @if ($item['short_url'])
                                    <a class="news-link" href="{{ $item['short_url'] }}" @if ($item['is_external']) target="_blank" rel="noopener" @endif>
                                        {{ $item['is_external'] ? 'Deschide articolul original' : 'Vezi articolul complet' }}
                                    </a>
                                @endif
                            </div>
                        </details>
                    @empty
                        <p>Nu avem știri disponibile în acest moment. Reveniți mai târziu.</p>
                    @endforelse
                </div>
            </section>

        </div>
    </div>
@endsection
