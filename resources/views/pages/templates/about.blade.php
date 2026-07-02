@php
    $pages = app(\App\Services\PageService::class);
    $tagline = $pages->localizedMeta($page->meta, 'tagline', $locale);
    $values = $pages->localizedMeta($page->meta, 'values', $locale);
    $closing = $pages->localizedMeta($page->meta, 'closing', $locale);
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-page-template-shell :page="$page">
        @include('pages.partials.page-header', [
            'title' => $title,
            'lead' => $tagline,
            'page' => $page,
            'prominent' => true,
        ])

        <div class="page-section-band" aria-hidden="true">
            <span>{{ $pages->sectionLabel($page, $locale, 'site.pages.templates.about') }}</span>
        </div>

        <div class="template-about-grid">
            <article class="template-about-intro safehouse-glass safehouse-prose">
                {!! $body !!}
            </article>

            @if ($values)
                <section class="template-about-values" aria-labelledby="about-values-heading">
                    <h2 id="about-values-heading" class="template-about-values__heading">
                        {{ __('site.pages.about_values_heading') }}
                    </h2>
                    <div class="safehouse-prose template-about-values__body">
                        {!! nl2br(e($values)) !!}
                    </div>
                </section>
            @endif
        </div>

        @if ($closing)
            <blockquote class="template-about-closing safehouse-glass">
                <p>{{ $closing }}</p>
            </blockquote>
        @endif
    </x-page-template-shell>
@endsection
