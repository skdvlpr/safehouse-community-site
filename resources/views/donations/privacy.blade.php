@extends('layouts.donation')

@php
    $locale = app()->getLocale();
    $title = $campaign->getTranslation('title', $locale, false) ?: $campaign->getTranslation('title', 'it');
    $privacy = $campaign->getTranslation('privacy_notice', $locale, false) ?: $campaign->getTranslation('privacy_notice', 'it');
@endphp

@section('title', __('site.donations.privacy_title'))

@section('content')
    <h1 class="mb-2 text-3xl font-semibold">{{ __('site.donations.privacy_heading') }}</h1>
    <p class="mb-6 text-sm text-safehouse-muted">{{ $title }}</p>

    <div class="prose prose-invert max-w-none rounded-xl border border-white/10 bg-safehouse-modal p-6 text-safehouse-muted">
        @if ($privacy)
            {!! nl2br(e($privacy)) !!}
        @else
            <p>{{ __('site.donations.privacy_stripe') }}</p>
            <p>{{ __('site.donations.privacy_stored') }}</p>
        @endif
    </div>

    <a href="{{ route('donations.show', ['locale' => $locale, 'campaignSlug' => $campaign->slug]) }}"
       class="mt-6 inline-block text-safehouse-primary underline">{{ __('site.donations.privacy_back') }}</a>
@endsection
