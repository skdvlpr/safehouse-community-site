@php
    use App\Support\Navigation;

    $locale = app()->getLocale();
    $navItems = config('navigation.header', []);
@endphp

<header class="site-header">
    <div class="site-content site-header__bar">
        <button
            type="button"
            class="site-header__hamburger md:hidden"
            data-header-drawer-open
            aria-controls="site-header-drawer"
            aria-expanded="false"
            aria-label="{{ __('site.nav.menu') }}"
        >
            <span class="site-header__hamburger-bars" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </button>

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
               class="safehouse-btn-primary site-header__donate whitespace-nowrap">
                <span class="md:hidden">{{ __('site.nav.donate_short') }}</span>
                <span class="hidden md:inline">{{ __('site.nav.donate') }}</span>
            </a>
        </div>
    </div>
</header>

<div
    class="site-header-drawer md:hidden"
    id="site-header-drawer"
    data-header-drawer
    aria-hidden="true"
>
    <button
        type="button"
        class="site-header-drawer__backdrop"
        data-header-drawer-close
        tabindex="-1"
        aria-label="{{ __('site.nav.close_menu') }}"
    ></button>
    <nav class="site-header-drawer__panel" aria-label="{{ __('site.nav.menu') }}">
        <img
            src="{{ asset('images/logo.png') }}"
            alt=""
            class="site-header-drawer__watermark"
            aria-hidden="true"
            decoding="async"
        >
        <div class="site-header-drawer__head">
            <p class="site-header-drawer__title">{{ __('site.nav.menu') }}</p>
            <button
                type="button"
                class="site-header-drawer__close"
                data-header-drawer-close
                aria-label="{{ __('site.nav.close_menu') }}"
            >
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @foreach ($navItems as $item)
            @include('layouts.partials.nav-item-mobile', ['item' => $item, 'locale' => $locale])
        @endforeach
    </nav>
</div>
