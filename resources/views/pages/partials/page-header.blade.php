@props([
    'title',
    'lead' => null,
    'page' => null,
    'prominent' => false,
])

@if ($prominent)
    @include('pages.partials.page-hero', [
        'title' => $title,
        'tagline' => $lead,
        'page' => $page,
    ])
@else
    <header @class(['mb-8' => ! $page, 'mb-4' => (bool) $page])>
        <h1 class="page-title">{{ $title }}</h1>
        @if ($lead)
            <p class="page-title__lead">{{ $lead }}</p>
        @endif
    </header>

    @if ($page)
        @include('pages.partials.page-carousel', ['page' => $page])
    @endif
@endif
