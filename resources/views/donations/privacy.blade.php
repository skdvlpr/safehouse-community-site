@extends('layouts.donation')

@php
    $locale = app()->getLocale();
    $title = $campaign->getTranslation('title', $locale, false) ?: $campaign->getTranslation('title', 'it');
    $privacy = $campaign->getTranslation('privacy_notice', $locale, false) ?: $campaign->getTranslation('privacy_notice', 'it');
@endphp

@section('title', __('Privacy pagamenti'))

@section('content')
    <h1 class="mb-2 text-3xl font-semibold">{{ __('Informativa privacy — pagamenti') }}</h1>
    <p class="mb-6 text-sm text-safehouse-muted">{{ $title }}</p>

    <div class="prose prose-invert max-w-none rounded-xl border border-white/10 bg-safehouse-modal p-6 text-safehouse-muted">
        @if ($privacy)
            {!! nl2br(e($privacy)) !!}
        @else
            <p>{{ __('I pagamenti sono elaborati da Stripe Inc. Safe House non memorizza numeri di carta, CVV o dati di autenticazione bancaria.') }}</p>
            <p>{{ __('Conserviamo solo nome del donatore, importo, valuta e commento opzionale, registrati nel nostro CRM per contabilità.') }}</p>
        @endif
    </div>

    <a href="{{ route('donations.show', ['locale' => $locale, 'donationCampaign' => $campaign->slug]) }}"
       class="mt-6 inline-block text-safehouse-primary underline">{{ __('Torna alla raccolta') }}</a>
@endsection
