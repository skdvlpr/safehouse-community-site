@php
    $locale = app()->getLocale();
    $title = $page->getTranslation('title', $locale);
    $body = $page->getTranslation('body', $locale);
    $pages = app(\App\Services\PageService::class);
    $values = $pages->localizedMeta($page->meta, 'values', $locale);
    $closing = $pages->localizedMeta($page->meta, 'closing', $locale);
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    @include('pages.partials.page-header', ['title' => $title])

    <article class="safehouse-glass safehouse-prose mb-8 rounded-2xl p-8 md:p-10">
        {!! $body !!}
    </article>

    @if ($values)
        <section class="safehouse-glass rounded-2xl p-8 md:p-10" aria-labelledby="about-values-heading">
            <h2 id="about-values-heading" class="mb-4 text-xl font-semibold text-safehouse-primary md:text-2xl">
                {{ __('site.pages.about_values_heading') }}
            </h2>
            <div class="safehouse-prose text-safehouse-muted">
                {!! nl2br(e($values)) !!}
            </div>
        </section>
    @endif

    @if ($closing)
        <p class="mt-8 text-center text-lg font-medium text-safehouse-text md:text-xl">
            {{ $closing }}
        </p>
    @endif
@endsection
