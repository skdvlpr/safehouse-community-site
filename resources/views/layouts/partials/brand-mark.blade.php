@php
    $locale = $locale ?? app()->getLocale();
    $showWordmark = $showWordmark ?? false;
    $wordmarkClass = $wordmarkClass ?? ($showWordmark ? 'brand-wordmark brand-wordmark--footer' : '');

    if (isset($logoClass)) {
        $resolvedLogoClass = $logoClass;
    } elseif ($showWordmark) {
        $resolvedLogoClass = 'brand-emblem';
    } else {
        $resolvedLogoClass = 'h-12 w-12 object-contain sm:h-14 sm:w-14';
    }
@endphp

<a href="{{ route('home', ['locale' => $locale]) }}" @class([
    'group inline-flex shrink-0 items-center transition',
    'gap-3' => $showWordmark,
])>
    <img src="{{ asset('images/logo.png') }}"
         alt="{{ $showWordmark ? '' : 'Safe House Community' }}"
         @class([
             $resolvedLogoClass,
             'shrink-0 opacity-95 transition group-hover:opacity-100',
         ])
         @if ($showWordmark) aria-hidden="true" @endif
         decoding="async">

    @if ($showWordmark)
        <span @class([$wordmarkClass, 'brand-wordmark'])>Safe House</span>
    @endif
</a>
