@php
    use App\Support\LocalizedUrl;

    $currentLocale = app()->getLocale();
    $labels = ['it' => 'IT', 'ru' => 'RU', 'en' => 'EN'];
@endphp

<nav class="locale-switcher"
     aria-label="Language">
    @foreach (config('locales.available', []) as $code)
        @if ($code === $currentLocale)
            <span class="rounded px-2 py-1 bg-safehouse-primary text-white" aria-current="true">
                {{ $labels[$code] ?? strtoupper($code) }}
            </span>
        @else
            <a href="{{ LocalizedUrl::forLocale($code) }}"
               class="rounded px-2 py-1 text-safehouse-muted transition hover:bg-white/5 hover:text-safehouse-text">
                {{ $labels[$code] ?? strtoupper($code) }}
            </a>
        @endif
    @endforeach
</nav>
