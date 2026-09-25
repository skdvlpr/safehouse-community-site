@php
    $pages = app(\App\Services\PageService::class);
    $tagline = $pages->localizedMeta($page->meta, 'tagline', $locale);
    $values = $pages->localizedMeta($page->meta, 'values', $locale);
    $closing = $pages->localizedMeta($page->meta, 'closing', $locale);
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-page-template-shell :page="$page" class="motion-enter">
        @include('pages.partials.page-header', [
            'title' => $title,
            'lead' => $tagline,
            'page' => $page,
            'prominent' => true,
        ])

        <div class="template-about-grid">
            <div class="landing-reveal lg:col-span-7" data-reveal-from="left">
                <article class="template-about-intro landing-reveal__target safehouse-glass safehouse-prose">
                    {!! \App\Support\CmsHtml::render($body) !!}
                </article>
            </div>

            @if ($values)
                <div class="landing-reveal lg:col-span-5" data-reveal-from="right">
                    <section class="template-about-values landing-reveal__target" aria-labelledby="about-values-heading">
                        <h2 id="about-values-heading" class="template-about-values__heading">
                            {{ __('site.pages.about_values_heading') }}
                        </h2>
                        <div class="safehouse-prose template-about-values__body">
                            {!! \App\Support\CmsHtml::render($values) !!}
                        </div>
                    </section>
                </div>
            @endif
        </div>

        @if ($closing)
            <div class="landing-reveal" data-reveal-from="up">
                <blockquote class="template-about-closing landing-reveal__target safehouse-glass safehouse-prose">
                    {!! \App\Support\CmsHtml::render($closing) !!}
                </blockquote>
            </div>
        @endif
    </x-page-template-shell>
@endsection
