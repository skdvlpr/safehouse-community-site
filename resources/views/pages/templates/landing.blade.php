
@extends('layouts.app')

@section('title', $title)

@section('main_class', 'flex-1 w-full px-4 py-6 md:py-10')

@section('content')
    <x-page-template-shell :page="$page">
        <section class="template-landing-hero">
            @include('pages.partials.section-label', [
                'page' => $page,
                'locale' => $locale,
                'fallbackKey' => 'site.pages.templates.landing',
            ])

            <h1 class="relative mb-6 max-w-4xl text-4xl font-semibold tracking-tight md:text-5xl lg:text-6xl">
                {{ $title }}
            </h1>

            @include('pages.partials.page-carousel', ['page' => $page])

            <div class="safehouse-prose relative max-w-2xl text-lg text-safehouse-muted md:text-xl">
                {!! \App\Support\CmsHtml::render($body) !!}
            </div>

            <div class="relative mt-8 flex flex-wrap gap-3">
                <a href="{{ route('donations.index', ['locale' => $locale]) }}" class="safehouse-btn-primary">
                    {{ __('site.home.cta_donate') }}
                </a>
                <a href="{{ app(\App\Services\PageService::class)->urlForKey('contact', $locale) ?? '#' }}"
                   class="inline-flex items-center justify-center rounded-md border border-white/20 px-4 py-2 text-sm font-medium text-safehouse-text transition hover:border-white/40">
                    {{ __('site.nav.contact') }}
                </a>
            </div>
        </section>
    </x-page-template-shell>
@endsection
