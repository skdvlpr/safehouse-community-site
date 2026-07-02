@extends('layouts.app')

@section('title', $title)

@section('content')
    @php
        $pages = app(\App\Services\PageService::class);
        $eyebrow = $pages->localizedMeta($page->meta, 'eyebrow', $locale);
        $statsHeading = $pages->localizedMeta($page->meta, 'stats_heading', $locale)
            ?: __('site.home.stats.heading');
        $statsLead = $pages->localizedMeta($page->meta, 'stats_lead', $locale)
            ?: __('site.home.stats.lead');
        $ctaDonate = $pages->localizedMeta($page->meta, 'cta_donate', $locale)
            ?: __('site.home.cta_donate');
        $ctaVolunteer = $pages->localizedMeta($page->meta, 'cta_volunteer', $locale)
            ?: __('site.home.cta_volunteer');
        $primaryTagline = app(\App\Services\SiteContentService::class)->primaryTagline($locale);
    @endphp

    <x-page-template-shell :page="$page">
        <section class="safehouse-glass mb-10 rounded-2xl p-8 md:p-12">
            @if ($eyebrow)
                <p class="mb-3 text-sm font-medium uppercase tracking-wider text-safehouse-primary">
                    {{ $eyebrow }}
                </p>
            @endif

            <h1 class="mb-3 max-w-3xl text-3xl font-semibold tracking-tight md:text-4xl lg:text-5xl">
                {{ $title }}
            </h1>

            @if ($primaryTagline !== '')
                <p class="mb-8 max-w-2xl text-lg text-safehouse-muted md:text-xl">
                    {{ $primaryTagline }}
                </p>
            @endif

            @include('pages.partials.page-carousel', ['page' => $page])

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('donations.index', ['locale' => $locale]) }}" class="safehouse-btn-primary">
                    {{ $ctaDonate }}
                </a>
                <a href="{{ route('volunteers.show', ['locale' => $locale]) }}" class="safehouse-btn-secondary">
                    {{ $ctaVolunteer }}
                </a>
            </div>
        </section>

        <section aria-labelledby="home-stats-heading">
            @include('pages.partials.section-label', [
                'page' => $page,
                'locale' => $locale,
                'fallbackKey' => 'site.home.stats.heading',
                'variant' => 'eyebrow',
            ])

            <div class="mb-6 flex items-end justify-between gap-4">
                <div>
                    <h2 id="home-stats-heading" class="text-xl font-semibold tracking-tight md:text-2xl">
                        {{ $statsHeading }}
                    </h2>
                    @if ($statsLead)
                        <p class="mt-1 text-sm text-safehouse-muted">{{ $statsLead }}</p>
                    @endif
                </div>
            </div>

            @include('pages.partials.home-meal-stats', ['mealStats' => $mealStats])
        </section>
    </x-page-template-shell>
@endsection
