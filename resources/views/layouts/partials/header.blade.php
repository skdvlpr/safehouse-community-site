@php
    use App\Support\Navigation;

    $locale = app()->getLocale();
    $navItems = config('navigation.header', []);
@endphp

<header class="sticky top-0 z-50 border-b border-white/10 bg-safehouse-page/90 backdrop-blur-md">
    <div class="site-content flex items-center justify-between gap-4 py-4">
        @include('layouts.partials.brand-mark', [
            'locale' => $locale,
            'showWordmark' => true,
            'wordmarkClass' => 'brand-wordmark--header',
        ])

        <nav class="hidden items-center gap-6 md:flex" aria-label="{{ __('site.nav.menu') }}">
            @foreach ($navItems as $item)
                @include('layouts.partials.nav-item', ['item' => $item, 'locale' => $locale])
            @endforeach
        </nav>

        <div class="flex items-center gap-3 sm:gap-4">
            @include('layouts.partials.locale-switcher')

            <a href="{{ route('donations.index', ['locale' => $locale]) }}"
               class="safehouse-btn-primary hidden whitespace-nowrap sm:inline-flex">
                {{ __('site.nav.donate') }}
            </a>

            <details class="relative md:hidden">
                <summary class="cursor-pointer list-none rounded-md border border-white/10 px-3 py-2 text-sm text-safehouse-muted hover:text-safehouse-text [&::-webkit-details-marker]:hidden">
                    {{ __('site.nav.menu') }}
                </summary>
                <div class="absolute right-0 z-50 mt-2 min-w-48 rounded-lg border border-white/10 bg-safehouse-modal p-2 shadow-lg">
                    @foreach ($navItems as $item)
                        @include('layouts.partials.nav-item-mobile', ['item' => $item, 'locale' => $locale])
                    @endforeach
                    <a href="{{ route('donations.index', ['locale' => $locale]) }}"
                       class="safehouse-btn-primary mt-2 block text-center">
                        {{ __('site.nav.donate') }}
                    </a>
                </div>
            </details>
        </div>
    </div>
</header>
