@extends('layouts.app')

@section('title', $post->title . ' · craiova.ro')

@section('meta')
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="{{ $post->title }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($post->hero_image_url)
        <meta property="og:image" content="{{ $post->hero_image_url }}">
    @endif
@endsection

@section('content')
    <div class="page">
        <div class="content content--article">
            <section class="panel article-panel">
                <header class="hero article-header">
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
            </section>

        </div>
    </div>
@endsection
