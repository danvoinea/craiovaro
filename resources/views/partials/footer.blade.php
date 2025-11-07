<footer class="site-footer">
    <div class="site-container site-footer__inner">
        <p class="site-footer__copy">&copy; {{ now()->year }} craiova.ro · Știri din Craiova.</p>

        <div class="site-footer__links">
            <a href="{{ route('home.show') }}">Flux principal</a>
            <a href="#top" class="site-footer__back-to-top">Înapoi sus</a>
        </div>
    </div>
</footer>
