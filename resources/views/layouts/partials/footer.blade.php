@php
    use App\Support\Navigation;

    $locale = app()->getLocale();
    $footerItems = config('navigation.footer', []);
    $primaryTagline = app(\App\Services\SiteContentService::class)->primaryTagline($locale);
@endphp

<footer class="mt-auto border-t border-white/10 bg-safehouse-modal/50">
    <div class="site-content py-5 md:py-6">
        <div class="footer-bar">
            <div class="footer-brand">
                @include('layouts.partials.brand-mark', [
                    'locale' => $locale,
                    'showWordmark' => true,
                    'wordmarkClass' => 'brand-wordmark--footer',
                ])

                @if ($primaryTagline !== '')
                    <p class="footer-brand__tagline">{{ $primaryTagline }}</p>
                @endif
            </div>

            <nav class="footer-nav" aria-label="Footer">
                @foreach ($footerItems as $item)
                    <a href="{{ Navigation::url($item, $locale) }}"
                       class="footer-nav__link">
                        {{ __($item['label']) }}
                    </a>
                @endforeach
            </nav>
        </div>

        <p class="footer-copyright">
            {{ __('site.footer.rights', ['year' => now()->year]) }}
        </p>
    </div>
</footer>
