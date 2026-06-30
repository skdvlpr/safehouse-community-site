<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Donazioni')) — {{ config('app.name') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @stack('head')
</head>
<body class="min-h-screen bg-safehouse-page text-safehouse-text antialiased">
    <div class="mx-auto max-w-3xl px-4 py-10">
        <header class="mb-8">
            <a href="{{ route('donations.index', ['locale' => app()->getLocale()]) }}" class="text-sm text-safehouse-muted hover:text-safehouse-primary">
                ← {{ __('Tutte le raccolte') }}
            </a>
        </header>
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
