@php
    use App\Support\Navigation;

    $locale = app()->getLocale();
    $navItems = config('navigation.header', []);
@endphp

<header class="site-header sticky top-0 z-50 border-b border-white/10 bg-safehouse-page/90 backdrop-blur-md">
    <div class="site-content site-header__bar">
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

        <div class="site-header__actions">
            @include('layouts.partials.display-prefs')

            <a href="{{ route('donations.index', ['locale' => $locale]) }}"
               class="safehouse-btn-primary hidden whitespace-nowrap sm:inline-flex">
                {{ __('site.nav.donate') }}
            </a>

            <details class="site-header__menu md:hidden">
                <summary class="site-header__menu-trigger">
                    {{ __('site.nav.menu') }}
                </summary>
                <div class="site-header__menu-panel">
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
