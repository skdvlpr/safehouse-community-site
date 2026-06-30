@php
    use App\Support\Navigation;

    $locale = app()->getLocale();
    $footerItems = config('navigation.footer', []);
@endphp

<footer class="mt-auto border-t border-white/10 bg-safehouse-modal/50">
    <div class="site-content py-10">
        <div class="footer-bar">
            @include('layouts.partials.brand-mark', [
                'locale' => $locale,
                'showWordmark' => true,
                'wordmarkClass' => 'brand-wordmark--footer',
            ])

            <nav class="footer-nav" aria-label="Footer">
                @foreach ($footerItems as $item)
                    <a href="{{ Navigation::url($item, $locale) }}"
                       class="footer-nav__link">
                        {{ __($item['label']) }}
                    </a>
                @endforeach
            </nav>
        </div>

        <p class="mt-5 max-w-sm text-sm text-safehouse-muted">{{ __('site.footer.tagline') }}</p>

        <p class="mt-8 border-t border-white/10 pt-6 text-xs text-safehouse-muted">
            {{ __('site.footer.rights', ['year' => now()->year]) }}
        </p>
    </div>
</footer>
