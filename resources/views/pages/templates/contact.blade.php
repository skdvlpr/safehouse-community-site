
@extends('layouts.app')

@section('title', $title)

@section('content')
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
            </article>

            <aside class="template-contact-aside safehouse-glass">
                <h2 class="mb-4 text-lg font-semibold">{{ __('site.pages.contact_form_heading') }}</h2>
                @include('pages.partials.contact-form-shell')
            </aside>
        </div>
    </x-page-template-shell>
@endsection

@if (app(\App\Services\TurnstileVerifier::class)->enabled())
    @push('head')
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endpush
@endif
