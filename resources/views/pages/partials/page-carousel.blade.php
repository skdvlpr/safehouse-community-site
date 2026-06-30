@props([
    'page',
])

@php
    $carouselSlides = \App\Support\PageCarousel::slides($page->meta ?? null, app()->getLocale());
@endphp

@if (count($carouselSlides) > 0)
    @include('pages.partials.media-carousel', ['slides' => $carouselSlides])
@endif
