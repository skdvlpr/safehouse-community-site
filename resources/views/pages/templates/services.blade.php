@php
    $pages = app(\App\Services\PageService::class);
    $cards = $pages->localizedServiceCards($page->meta, $locale);
    $tagline = __('site.pages.services_tagline');
    $bodyText = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $body)) ?? '');
    $bodyRepeatsTagline = $bodyText !== '' && $bodyText === $tagline;
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-page-template-shell :page="$page" class="motion-enter">
        @include('pages.partials.page-header', [
            'title' => $title,
            'lead' => $tagline,
            'prominent' => true,
        ])

        @include('pages.partials.page-carousel', ['page' => $page])

        @if ($body && ! $bodyRepeatsTagline)
            <div class="template-services-banner safehouse-prose">
                {!! \App\Support\CmsHtml::render($body) !!}
            </div>
        @endif

        <div class="template-services-grid">
            @foreach ($cards as $index => $card)
                <article @class([
                    'landing-reveal',
                    'template-service-card--span-full' => $loop->last && $loop->count % 2 !== 0,
                ]) data-reveal-from="{{ $index % 2 === 0 ? 'left' : 'right' }}">
                    <div class="template-service-card landing-reveal__target flex flex-col">
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
                    </div>
                </article>
            @endforeach
        </div>
    </x-page-template-shell>
@endsection
