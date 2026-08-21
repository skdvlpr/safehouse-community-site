@extends('layouts.app')

@section('title', $title)

@section('content')
    @php
        $pages = app(\App\Services\PageService::class);
        $eyebrow = $pages->localizedMeta($page->meta, 'eyebrow', $locale);
        $ctaDonate = $pages->localizedMeta($page->meta, 'cta_donate', $locale)
            ?: __('site.home.cta_donate');
        $ctaVolunteer = $pages->localizedMeta($page->meta, 'cta_volunteer', $locale)
            ?: __('site.home.cta_volunteer');
        $ctaContact = $pages->localizedMeta($page->meta, 'cta_contact', $locale)
            ?: __('site.home.cta_contact');
        $contactUrl = \App\Support\Navigation::url(['page_key' => 'contact'], $locale);
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
                <p class="mb-8 max-w-2xl text-lg text-safehouse-muted lg:max-w-none lg:text-xl xl:whitespace-nowrap">
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
                <a href="{{ $contactUrl }}" class="safehouse-btn-secondary">
                    {{ $ctaContact }}
                </a>
            </div>
        </section>

        <section class="mb-10" aria-label="{{ __('site.home.stats.heading') }}">
            @include('pages.partials.home-impact-stats', ['impactStats' => $impactStats])
        </section>

        @include('pages.partials.home-manifesto-banner')
    </x-page-template-shell>
@endsection
