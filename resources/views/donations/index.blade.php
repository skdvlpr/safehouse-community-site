@extends('layouts.donation')

@section('title', __('site.donations.index_title'))

@section('content')
    @php
        $locale = app()->getLocale();
        $five = $donationSettings->fivePerMille();
    @endphp

    <h1 class="mb-2 text-3xl font-semibold">{{ __('site.donations.index_title') }}</h1>
    <p class="mb-8 text-safehouse-muted">{{ __('site.donations.index_lead') }}</p>

    @if ($donationSettings->fivePerMilleEnabled())
        <a href="{{ route('donations.five-per-mille', ['locale' => $locale]) }}"
           class="donation-feature-card mb-8 block rounded-2xl border border-safehouse-primary/40 bg-gradient-to-br from-safehouse-primary/20 via-safehouse-primary/5 to-transparent p-6 transition hover:border-safehouse-primary md:p-8">
            <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-safehouse-primary">
                {{ $donationSettings->localized($five, 'menu_label', $locale) }}
            </p>
            <h2 class="mb-3 text-2xl font-semibold md:text-3xl">
                {{ $donationSettings->localized($five, 'heading', $locale) }}
            </h2>
            @if ($donationSettings->localized($five, 'lead', $locale) !== '')
                <p class="mb-4 max-w-2xl text-safehouse-muted">
                    {{ $donationSettings->localized($five, 'lead', $locale) }}
                </p>
            @endif
            <span class="safehouse-btn-primary inline-flex">{{ __('site.donations.five_per_mille_cta') }}</span>
        </a>
    @endif

    @include('donations.partials.bank-transfer', ['donationSettings' => $donationSettings])

    @if ($recurringCampaign)
        @php
            $recurringTitle = $recurringCampaign->getTranslation('title', $locale, false)
                ?: $recurringCampaign->getTranslation('title', 'it');
            $recurringDescription = $recurringCampaign->getTranslation('description', $locale, false)
                ?: $recurringCampaign->getTranslation('description', 'it');
        @endphp
        <a href="{{ route('donations.show', ['locale' => $locale, 'campaignSlug' => $recurringCampaign->slug]) }}"
           class="donation-feature-card mb-8 block rounded-2xl border border-safehouse-primary/40 bg-gradient-to-br from-safehouse-primary/20 via-safehouse-primary/5 to-transparent p-6 transition hover:border-safehouse-primary md:p-8">
            <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-safehouse-primary">
                {{ __('site.donations.recurring_badge') }}
            </p>
            <h2 class="mb-3 text-2xl font-semibold md:text-3xl">{{ $recurringTitle }}</h2>
            @if ($recurringDescription)
                <p class="mb-4 max-w-2xl text-safehouse-muted">
                    {{ Str::limit(strip_tags($recurringDescription), 180) }}
                </p>
            @endif
            <span class="safehouse-btn-primary inline-flex">{{ __('site.donations.recurring_cta') }}</span>
        </a>
    @endif

    <h2 class="mb-4 text-xl font-semibold">{{ __('site.donations.online_campaigns_heading') }}</h2>

    <div class="space-y-4">
        @forelse ($campaigns as $campaign)
            <a href="{{ route('donations.show', ['locale' => $locale, 'campaignSlug' => $campaign->slug]) }}"
               class="block rounded-xl border border-white/10 bg-safehouse-modal p-6 transition hover:border-safehouse-primary">
                <h3 class="text-xl font-medium">{{ $campaign->getTranslation('title', $locale, false) ?: $campaign->getTranslation('title', 'it') }}</h3>
                @isset($progressBySlug[$campaign->slug])
                    <div class="mt-3">
                        @include('donations.partials.fundraising-progress', ['progress' => $progressBySlug[$campaign->slug]])
                    </div>
                @endisset
                @if ($campaign->getTranslation('description', $locale, false))
                    <p class="mt-2 text-sm text-safehouse-muted">{{ Str::limit(strip_tags($campaign->getTranslation('description', $locale)), 160) }}</p>
                @endif
            </a>
        @empty
            <p class="text-safehouse-muted">{{ __('Nessuna raccolta attiva al momento.') }}</p>
        @endforelse
    </div>
@endsection
