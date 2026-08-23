@php
    $pages = app(\App\Services\PageService::class);
    $cards = $pages->localizedServiceCards($page->meta, $locale);
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-page-template-shell :page="$page">
        @include('pages.partials.section-label', [
            'page' => $page,
            'locale' => $locale,
            'fallbackKey' => 'site.pages.templates.services',
        ])

        @if ($body)
            <div class="template-services-banner safehouse-prose">
                <h1 class="mb-3 text-3xl font-semibold tracking-tight md:text-4xl">{{ $title }}</h1>

                @include('pages.partials.page-carousel', ['page' => $page])

                {!! \App\Support\CmsHtml::render($body) !!}
            </div>
        @else
            @include('pages.partials.page-header', ['title' => $title, 'page' => $page])
        @endif

        <div class="template-services-grid">
            @foreach ($cards as $index => $card)
                <article @class([
                    'template-service-card flex flex-col',
                    'template-service-card--span-full' => $loop->last && $loop->count % 2 !== 0,
                ])>
                    <span class="template-service-card__index">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                    <h2 class="mb-3 text-lg font-semibold text-safehouse-text">{{ $card['title'] }}</h2>
                    <div class="safehouse-prose flex-1 text-sm text-safehouse-muted">
                        {!! \App\Support\CmsHtml::render($card['body'] ?? '') !!}
                    </div>
                    @if ($card['stats'])
                        <p class="mt-4 border-t border-white/10 pt-4 text-xs font-medium uppercase tracking-wide text-safehouse-primary">
                            {{ $card['stats'] }}
                        </p>
                    @endif
                </article>
            @endforeach
        </div>
    </x-page-template-shell>
@endsection
