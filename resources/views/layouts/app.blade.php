<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark" data-theme-pref="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) — Safe House</title>
    <script>
        (function () {
            try {
                var key = 'safehouse.theme';
                var pref = localStorage.getItem(key);
                if (pref !== 'dark' && pref !== 'light' && pref !== 'system') {
                    pref = 'dark';
                }
                var resolved = pref === 'system'
                    ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                    : pref;
                document.documentElement.setAttribute('data-theme', resolved);
                document.documentElement.setAttribute('data-theme-pref', pref);
                document.documentElement.style.colorScheme = resolved;
            } catch (e) {}
        })();
    </script>
    @include('layouts.partials.favicons')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @include('layouts.partials.site-background')
    @stack('head')
</head>
<body class="flex min-h-screen flex-col bg-safehouse-page text-safehouse-text antialiased">
    @if (request()->routeIs('pages.preview', 'articles.preview', 'editorial-articles.preview') || ($isPreview ?? false))
        @include('layouts.partials.preview-banner')
    @endif

    @include('layouts.partials.header')

    <main class="@yield('main_class', 'site-content flex-1 py-10')">
        @yield('content')
    </main>

    @include('layouts.partials.footer')

    @include('layouts.partials.cookie-banner')

    @stack('scripts')
</body>
</html>
