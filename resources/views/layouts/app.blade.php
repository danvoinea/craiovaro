<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'craiova.ro')</title>
    @yield('meta')
    @vite(['resources/css/app.css', 'resources/css/site.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body id="top" class="site-body @yield('body-class')">
    <div class="site-shell">
        @include('partials.header')

        <main class="site-main">
            @yield('content')
        </main>

        @include('partials.footer')
    </div>

    @stack('scripts')
</body>
</html>
