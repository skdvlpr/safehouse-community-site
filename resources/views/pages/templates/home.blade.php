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
        $ctaMember = $pages->localizedMeta($page->meta, 'cta_member', $locale)
            ?: __('site.home.cta_member');
        $ctaContact = $pages->localizedMeta($page->meta, 'cta_contact', $locale)
            ?: __('site.home.cta_contact');
        $contactUrl = \App\Support\Navigation::url(['page_key' => 'contact'], $locale);
        $memberUrl = $pages->urlForKey('diventa-socio', $locale);
        $communityTagline = __('site.footer.tagline');
    @endphp

    <x-page-template-shell :page="$page" class="motion-enter">
        <section class="safehouse-glass mb-4 rounded-2xl p-5 md:mb-6 md:p-8 lg:p-10">
            @if ($eyebrow)
                <p class="mb-3 text-sm font-medium uppercase tracking-wider text-safehouse-primary">
                    {{ $eyebrow }}
                </p>
            @endif

            <h1 class="mb-3 text-3xl font-semibold tracking-tight md:text-5xl lg:text-6xl">
                {{ $title }}
            </h1>

            @if ($communityTagline !== '')
                <p class="mb-8 max-w-2xl text-lg text-safehouse-muted lg:max-w-none lg:text-xl xl:whitespace-nowrap">
                    {{ $communityTagline }}
                </p>
            @endif

            @include('pages.partials.page-carousel', ['page' => $page])

            <div class="flex flex-wrap items-center gap-3">
                @if ($memberUrl)
                    <a href="{{ $memberUrl }}" class="safehouse-btn-primary">
                        {{ $ctaMember }}
                    </a>
                @endif
                <a href="{{ route('donations.index', ['locale' => $locale]) }}" class="safehouse-btn-secondary">
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

        @include('pages.partials.home-manifesto-banner')

        @include('pages.partials.latest-stories', ['latestStories' => $latestStories ?? [], 'locale' => $locale])

        @include('pages.partials.home-independence-banner', ['locale' => $locale])

        <section class="mb-10" aria-label="{{ __('site.home.stats.heading') }}">
            @include('pages.partials.home-impact-stats', ['impactStats' => $impactStats])
        </section>
    </x-page-template-shell>
@endsection
