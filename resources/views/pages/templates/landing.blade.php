@extends('layouts.app')

@section('title', $title)

@section('main_class', 'flex-1 w-full px-4 py-6 md:py-8')

@section('content')
    @php
        $pages = app(\App\Services\PageService::class);
        $layout = \App\Support\LandingContent::fromPage($page, $locale, $body);
        $isMembership = $page->key === 'diventa-socio';
        $contactUrl = $pages->urlForKey('contact', $locale);
        $faqUrl = $pages->urlForKey('faq', $locale);
        $statutoUrl = $faqUrl;
        $hasVisual = count(\App\Support\PageCarousel::slides($page->meta ?? null, $locale)) > 0;
        $contactHeading = $layout->contactHeading !== ''
            ? $layout->contactHeading
            : __('site.membership.contact_heading');
    @endphp

    <x-page-template-shell :page="$page">
        @if ($layout->values !== [])
            <section class="landing-marquee" aria-label="{{ __('site.membership.values_label') }}">
                <div class="landing-marquee__bar">
                    <p class="landing-marquee__kicker">{{ __('site.membership.values_label') }}</p>
                    <div class="landing-marquee__viewport">
                        <div class="landing-marquee__track">
                            <div class="landing-marquee__group" data-marquee-source>
                                <span class="landing-marquee__mobile-label">
                                    <span class="landing-marquee__item landing-marquee__item--label">{{ __('site.membership.values_label') }}</span>
                                    <span class="landing-marquee__dot" aria-hidden="true">·</span>
                                </span>
                                @foreach ($layout->values as $value)
                                    <span class="landing-marquee__item">{{ $value }}</span>
                                    <span class="landing-marquee__dot" aria-hidden="true">·</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <section @class(['template-landing-hero', 'template-landing-hero--copy-only' => ! $hasVisual])>
            <div class="template-landing-copy">
                @include('pages.partials.section-label', [
                    'page' => $page,
                    'locale' => $locale,
                    'fallbackKey' => '',
                ])

                <h1 class="relative text-4xl font-bold tracking-tight md:text-5xl lg:text-6xl">
                    {{ $title }}
                </h1>

                @if ($layout->intro !== '')
                    <div class="safehouse-prose relative text-base text-safehouse-text md:text-lg">
                        {!! \App\Support\CmsHtml::render($layout->intro) !!}
                    </div>
                @endif

                <div class="template-landing-actions">
                    @if ($isMembership)
                        <button type="button" class="safehouse-btn-primary" data-socio-open>
                            {{ __('site.membership.apply') }}
                        </button>
                    @else
                        <a href="{{ route('donations.index', ['locale' => $locale]) }}" class="safehouse-btn-primary">
                            {{ __('site.home.cta_donate') }}
                        </a>
                        @if ($contactUrl)
                            <a href="{{ $contactUrl }}"
                               class="inline-flex items-center justify-center rounded-md border px-4 py-2 text-sm font-semibold text-safehouse-text transition"
                               style="border-color: var(--safehouse-glass-border)">
                                {{ __('site.nav.contact') }}
                            </a>
                        @endif
                    @endif
                </div>
            </div>

            @if ($hasVisual)
                <div class="template-landing-visual">
                    @include('pages.partials.page-carousel', ['page' => $page])
                </div>
            @endif
        </section>

        @if ($layout->cards !== [])
            <p class="landing-swipe" aria-hidden="true">
                <span class="landing-swipe__label">{{ __('site.membership.swipe_hint') }}</span>
                <span class="landing-swipe__arrow">↓</span>
            </p>

            <div class="template-landing-cards">
                @foreach ($layout->cards as $index => $card)
                    {{-- Outer element stays in the grid flow so IntersectionObserver sees it; the inner card is what slides in from off-screen. --}}
                    <article class="landing-reveal" data-reveal-from="{{ $index % 2 === 0 ? 'left' : 'right' }}">
                        <div class="template-landing-card landing-reveal__target safehouse-glass safehouse-prose">
                            {!! \App\Support\CmsHtml::render($card) !!}
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if ($isMembership)
            <section class="landing-contact landing-reveal" data-reveal-from="up">
                <div class="landing-contact__inner">
                    <div class="landing-contact__main">
                        <h2 class="landing-contact__title">{{ $contactHeading }}</h2>
                        <p class="landing-contact__lead">{{ __('site.membership.contact_lead') }}</p>
                        <button type="button" class="safehouse-btn-primary" data-socio-open>
                            {{ __('site.membership.apply') }}
                        </button>
                    </div>

                    <nav class="landing-contact__links" aria-label="{{ $contactHeading }}">
                        @if ($statutoUrl)
                            <a class="landing-contact__link" href="{{ $statutoUrl }}">{{ __('site.membership.statuto') }}</a>
                        @endif
                        @if ($faqUrl)
                            <a class="landing-contact__link" href="{{ $faqUrl }}">{{ __('site.membership.faq') }}</a>
                        @endif
                        <a class="landing-contact__link" href="{{ route('donations.index', ['locale' => $locale]) }}">{{ __('site.membership.link_donate') }}</a>
                        <a class="landing-contact__link" href="{{ route('volunteers.show', ['locale' => $locale]) }}">{{ __('site.membership.link_volunteer') }}</a>
                    </nav>

                    <div class="landing-seat">
                        <div class="landing-seat__places">
                            <p><strong>{{ __('site.org.legal_seat_label') }}</strong><br>{{ __('site.org.legal_seat') }}</p>
                            <p><strong>{{ __('site.org.operative_seat_label') }}</strong> {{ __('site.org.operative_seat') }}</p>
                            <p>{{ __('site.org.fiscal_code') }}</p>
                        </div>
                        <p class="landing-seat__runts">{{ __('site.org.runts') }}</p>
                    </div>
                </div>
            </section>

            @include('pages.partials.membership-dialog')
        @endif
    </x-page-template-shell>
@endsection

@if ($page->key === 'diventa-socio' && app(\App\Services\TurnstileVerifier::class)->enabled())
    @push('scripts')
        <script>
            (function () {
                var theme = document.documentElement.getAttribute('data-theme');
                if (theme !== 'light' && theme !== 'dark') {
                    theme = 'dark';
                }
                document.querySelectorAll('.cf-turnstile').forEach(function (el) {
                    el.setAttribute('data-theme', theme);
                });
            })();
        </script>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endpush
@endif
