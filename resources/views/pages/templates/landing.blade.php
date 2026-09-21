@extends('layouts.app')

@section('title', $title)

@section('main_class', 'flex-1 w-full px-4 py-6 md:py-8')

@section('content')
    @php
        $sections = \App\Support\LandingBody::sections($body);
        $intro = $sections[0] ?? '';
        $cards = array_slice($sections, 1);
        $isMembership = $page->key === 'diventa-socio';
        $contactUrl = app(\App\Services\PageService::class)->urlForKey('contact', $locale);
        $hasVisual = count(\App\Support\PageCarousel::slides($page->meta ?? null, $locale)) > 0;
    @endphp

    <x-page-template-shell :page="$page">
        <section @class(['template-landing-hero', 'template-landing-hero--copy-only' => ! $hasVisual])>
            <div class="template-landing-copy">
                @include('pages.partials.section-label', [
                    'page' => $page,
                    'locale' => $locale,
                    'fallbackKey' => 'site.pages.templates.landing',
                ])

                <h1 class="relative text-4xl font-bold tracking-tight md:text-5xl lg:text-6xl">
                    {{ $title }}
                </h1>

                @if ($intro !== '')
                    <div class="safehouse-prose relative text-base text-safehouse-text md:text-lg">
                        {!! \App\Support\CmsHtml::render($intro) !!}
                    </div>
                @endif

                <div class="template-landing-actions">
                    @if ($isMembership)
                        <button type="button" class="safehouse-btn-primary" data-socio-open>
                            {{ __('site.membership.apply') }}
                        </button>
                        @if ($contactUrl)
                            <a href="{{ $contactUrl }}"
                               class="inline-flex items-center justify-center rounded-md border px-4 py-2 text-sm font-semibold text-safehouse-text transition hover:border-safehouse-primary/50"
                               style="border-color: var(--safehouse-glass-border)">
                                {{ __('site.nav.contact') }}
                            </a>
                        @endif
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

        @if ($cards !== [])
            <div class="template-landing-cards">
                @foreach ($cards as $card)
                    <article class="template-landing-card safehouse-glass safehouse-prose">
                        {!! \App\Support\CmsHtml::render($card) !!}
                    </article>
                @endforeach
            </div>
        @endif

        @if ($isMembership)
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
