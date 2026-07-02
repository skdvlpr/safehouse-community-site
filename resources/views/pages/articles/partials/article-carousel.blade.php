@php
    $slides = \App\Support\PageCarousel::slides($article->meta ?? null, $locale ?? app()->getLocale());
@endphp

@if (count($slides) > 0)
    @include('pages.partials.media-carousel', ['slides' => $slides])
@endif
