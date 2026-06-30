@extends('layouts.donation')

@section('title', __('Donazioni'))

@section('content')
    <h1 class="mb-2 text-3xl font-semibold">{{ __('Sostieni Safe House') }}</h1>
    <p class="mb-8 text-safehouse-muted">{{ __('Scegli una raccolta e contribuisci in modo sicuro con Stripe.') }}</p>

    <div class="space-y-4">
        @forelse ($campaigns as $campaign)
            <a href="{{ route('donations.show', ['locale' => app()->getLocale(), 'campaignSlug' => $campaign->slug]) }}"
               class="block rounded-xl border border-white/10 bg-safehouse-modal p-6 transition hover:border-safehouse-primary">
                <h2 class="text-xl font-medium">{{ $campaign->getTranslation('title', app()->getLocale(), false) ?: $campaign->getTranslation('title', 'it') }}</h2>
                @if ($campaign->getTranslation('description', app()->getLocale(), false))
                    <p class="mt-2 text-sm text-safehouse-muted">{{ Str::limit(strip_tags($campaign->getTranslation('description', app()->getLocale())), 160) }}</p>
                @endif
            </a>
        @empty
            <p class="text-safehouse-muted">{{ __('Nessuna raccolta attiva al momento.') }}</p>
        @endforelse
    </div>
@endsection
