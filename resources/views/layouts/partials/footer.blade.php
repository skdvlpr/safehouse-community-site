@php
    use App\Support\Navigation;

    $locale = app()->getLocale();
    $footerItems = config('navigation.footer', []);
    $primaryTagline = app(\App\Services\SiteContentService::class)->primaryTagline($locale);
@endphp

<footer class="site-footer">
    <div class="site-content site-footer__inner">
        <div class="site-footer__brand">
            @include('layouts.partials.brand-mark', [
                'locale' => $locale,
                'showWordmark' => true,
                'wordmarkClass' => 'brand-wordmark--footer',
            ])

            @if ($primaryTagline !== '')
                <p class="site-footer__tagline">{{ $primaryTagline }}</p>
            @endif
        </div>

        <div class="site-footer__social">
            <x-social-links />
        </div>

        @if ($footerItems !== [])
            <nav class="site-footer__legal" aria-label="Footer">
                @foreach ($footerItems as $index => $item)
                    @if ($index > 0)
                        <span class="site-footer__legal-sep" aria-hidden="true">|</span>
                    @endif
                    <a href="{{ Navigation::url($item, $locale) }}" class="site-footer__legal-link">
                        {{ __($item['label']) }}
                    </a>
                @endforeach
            </nav>
        @endif

        <p class="site-footer__copy">
            {{ __('site.footer.rights', ['year' => now()->year]) }}
        </p>
    </div>
</footer>
