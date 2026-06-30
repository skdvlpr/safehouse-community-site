@props([
    'title',
    'tagline' => null,
    'page' => null,
])

<div class="page-hero mb-10">
    <div class="page-hero__headline">
        <h1 class="page-hero__title">{{ $title }}</h1>

        @if ($tagline)
            <div class="page-hero__divider" aria-hidden="true"></div>
            <p class="page-hero__tagline">{{ $tagline }}</p>
        @endif
    </div>

    @if ($page)
        @include('pages.partials.page-carousel', ['page' => $page])
    @endif
</div>
