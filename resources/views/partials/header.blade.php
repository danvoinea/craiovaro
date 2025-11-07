@php
    $lastUpdated = isset($refreshedAt) ? $refreshedAt->diffForHumans() : null;
@endphp

<header class="site-header">
    <div class="site-container site-header__inner">
        <div class="site-header__brand">
            <a href="{{ route('home.show') }}" class="site-header__logo">craiova.ro</a>

            @if ($lastUpdated)
                <p class="site-header__meta">
                    Actualizăm automat știrile locale din Craiova și Dolj.
                    <span aria-hidden="true">·</span>
                    Ultima actualizare: {{ $lastUpdated }}.
                </p>
            @endif
        </div>

        @hasSection('header-actions')
            <div class="site-header__actions">
                @yield('header-actions')
            </div>
        @endif
    </div>
</header>
