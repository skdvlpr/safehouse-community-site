@extends('layouts.donation')

@php
    $title = $campaign->getTranslation('title', app()->getLocale(), false) ?: $campaign->getTranslation('title', 'it');
@endphp

@section('title', __('Grazie'))

@section('content')
    <div class="rounded-xl border border-white/10 bg-safehouse-modal p-8 text-center">
        <h1 class="mb-4 text-3xl font-semibold text-safehouse-primary">{{ __('Grazie per il tuo sostegno!') }}</h1>
        <p class="text-safehouse-muted">{{ __('Il pagamento è stato ricevuto. La registrazione contabile verrà completata a breve.') }}</p>
        <p class="mt-2 text-sm text-safehouse-muted">{{ $title }}</p>
        <a href="{{ route('donations.index', ['locale' => app()->getLocale()]) }}"
           class="safehouse-btn-primary mt-8 inline-block rounded-lg px-6 py-3">{{ __('Altre raccolte') }}</a>
    </div>
@endsection
