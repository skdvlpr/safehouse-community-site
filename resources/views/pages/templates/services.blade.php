@php
    $locale = app()->getLocale();
    $title = $page->getTranslation('title', $locale);
    $body = $page->getTranslation('body', $locale);
    $pages = app(\App\Services\PageService::class);
    $cards = $pages->localizedServiceCards($page->meta, $locale);
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    @include('pages.partials.page-header', ['title' => $title])

    @if ($body)
        <div class="safehouse-glass safehouse-prose mb-10 rounded-2xl p-8 md:p-10">
            {!! $body !!}
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        @foreach ($cards as $card)
            <article class="safehouse-glass flex flex-col rounded-2xl p-6 md:p-8">
                <h2 class="mb-3 text-lg font-semibold text-safehouse-primary">{{ $card['title'] }}</h2>
                <div class="safehouse-prose flex-1 text-sm text-safehouse-muted">
                    {!! nl2br(e($card['body'])) !!}
                </div>
                @if ($card['stats'])
                    <p class="mt-4 border-t border-white/10 pt-4 text-xs font-medium uppercase tracking-wide text-safehouse-text">
                        {{ $card['stats'] }}
                    </p>
                @endif
            </article>
        @endforeach
    </div>
@endsection
