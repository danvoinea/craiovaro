@php
    $containerClass = trim('panel sidebar ' . ($sidebarClass ?? ''));
@endphp

<aside class="{{ $containerClass }}">
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
