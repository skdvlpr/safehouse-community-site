@php
    $pages = app(\App\Services\PageService::class);
    $values = $pages->localizedMeta($page->meta, 'values', $locale);
    $closing = $pages->localizedMeta($page->meta, 'closing', $locale);
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-page-template-shell :page="$page">
        @include('pages.partials.template-eyebrow', ['label' => __('site.pages.templates.about')])

        @include('pages.partials.page-header', ['title' => $title])

        <div class="grid gap-8 lg:grid-cols-5">
            <article class="template-about-intro safehouse-glass safehouse-prose lg:col-span-3">
                {!! $body !!}
            </article>

            @if ($values)
                <section class="template-about-values lg:col-span-2" aria-labelledby="about-values-heading">
                    <h2 id="about-values-heading" class="mb-4 text-lg font-semibold text-safehouse-primary">
                        {{ __('site.pages.about_values_heading') }}
                    </h2>
                    <div class="safehouse-prose text-sm text-safehouse-muted">
                        {!! nl2br(e($values)) !!}
                    </div>
                </section>
            @endif
        </div>

        @if ($closing)
            <blockquote class="template-about-closing">
                {{ $closing }}
            </blockquote>
        @endif
    </x-page-template-shell>
@endsection
