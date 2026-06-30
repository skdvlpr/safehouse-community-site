@props([
    'title',
    'lead' => null,
])

<header class="mb-8">
    <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">{{ $title }}</h1>
    @if ($lead)
        <p class="mt-3 max-w-3xl text-lg text-safehouse-muted">{{ $lead }}</p>
    @endif
</header>
