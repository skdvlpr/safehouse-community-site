@php
    /** @var \App\DataTransferObjects\HomeImpactStatsSnapshot $impactStats */
    $cards = $impactStats->cards(app()->getLocale());
@endphp

<div class="grid gap-4 sm:grid-cols-3">
    @foreach ($cards as $card)
        <article class="safehouse-glass rounded-xl p-6 text-center">
            <p class="text-3xl font-semibold tabular-nums text-safehouse-primary md:text-4xl">
                {{ $card['value'] }}
            </p>
            <p class="mt-2 text-sm text-safehouse-muted">{{ $card['label'] }}</p>
        </article>
    @endforeach
</div>
