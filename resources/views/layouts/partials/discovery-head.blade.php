@php
    $discovery = app(\App\Support\DiscoveryText::class)->present(get_defined_vars(), app()->getLocale());
@endphp
<title>{{ $discovery['title'] }} {{ __('site.layout.title_suffix') }}</title>
<meta name="description" content="{{ $discovery['description'] }}">
<link rel="canonical" href="{{ $discovery['canonical'] }}">
@if ($discovery['noindex'])
    <meta name="robots" content="noindex, nofollow">
@endif
@foreach ($discovery['alternates'] as $hreflang => $href)
    <link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $href }}">
@endforeach
<meta property="og:title" content="{{ $discovery['title'] }}">
<meta property="og:description" content="{{ $discovery['description'] }}">
<meta property="og:locale" content="{{ $discovery['ogLocale'] }}">
<meta property="og:image" content="{{ $discovery['image'] }}">
<meta property="og:url" content="{{ $discovery['canonical'] }}">
