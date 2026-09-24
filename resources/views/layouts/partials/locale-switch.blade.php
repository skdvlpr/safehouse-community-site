@php
    use App\Support\LocalizedUrl;

    $current = app()->getLocale();
    $variant = $variant ?? 'text';
@endphp

@if ($variant === 'banner')
    <nav class="cookie-consent__langs" aria-label="{{ __('site.locale.banner') }}">
        @foreach (['it', 'en'] as $code)
            <button
                type="button"
                class="cookie-consent__lang"
                data-banner-lang="{{ $code }}"
                @if ($code === $current) aria-current="true" @endif
                lang="{{ $code }}"
            >{{ strtoupper($code) }}</button>
        @endforeach
    </nav>
@else
    @php
        $other = $current === 'it' ? 'en' : 'it';
    @endphp
    <a
        class="{{ $linkClass ?? 'site-footer__legal-link' }}"
        href="{{ LocalizedUrl::forLocale($other) }}"
        hreflang="{{ $other }}"
        lang="{{ $other }}"
    >{{ $other === 'en' ? __('site.locale.english_version') : __('site.locale.italian_version') }}</a>
@endif
