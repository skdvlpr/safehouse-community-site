@php
    $locale = $locale ?? app()->getLocale();
    $logoClass = $logoClass ?? 'h-9 w-auto sm:h-10';
@endphp

<a href="{{ route('home', ['locale' => $locale]) }}" class="group inline-flex shrink-0 items-center">
    <img src="{{ asset('images/logo-horizontal.svg') }}"
         alt="Safe House Community"
         class="{{ $logoClass }} transition opacity-90 group-hover:opacity-100"
         width="176"
         height="40"
         decoding="async">
</a>
