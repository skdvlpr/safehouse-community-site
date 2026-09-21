@extends('layouts.app')

@section('title', $title)

@section('content')
    @php
        $faqUrl = app(\App\Services\PageService::class)->urlForKey('faq', $locale);
    @endphp

    <x-page-template-shell :page="$page">
        @include('pages.partials.section-label', [
            'page' => $page,
            'locale' => $locale,
            'fallbackKey' => 'site.pages.templates.contact',
        ])

        @include('pages.partials.page-header', ['title' => $title, 'lead' => __('site.pages.contact_lead'), 'page' => $page])

        <div class="grid gap-8 lg:grid-cols-2">
            <article class="template-contact-info safehouse-glass safehouse-prose">
                {!! \App\Support\CmsHtml::render($body) !!}

                <div class="template-contact-seat">
                    <p><strong>{{ __('site.org.legal_seat_label') }}</strong><br>{{ __('site.org.legal_seat') }}</p>
                    <p>{{ __('site.org.runts') }}</p>
                    <p>{{ __('site.org.fiscal_code') }}</p>
                    <p><strong>{{ __('site.org.operative_seat_label') }}</strong> {{ __('site.org.operative_seat') }}</p>
                </div>

                @if ($faqUrl)
                    <p class="template-contact-faq">
                        <a href="{{ $faqUrl }}" class="safehouse-btn-primary">{{ __('site.pages.contact_faq') }}</a>
                    </p>
                @endif
            </article>

            <aside class="template-contact-aside safehouse-glass">
                <h2 class="mb-4 text-lg font-semibold">{{ __('site.pages.contact_form_heading') }}</h2>
                @include('pages.partials.contact-form-shell')
            </aside>
        </div>
    </x-page-template-shell>
@endsection

@if (app(\App\Services\TurnstileVerifier::class)->enabled())
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
