@php
    $locale = app()->getLocale();
    $title = $article->getTranslation('title', $locale);
    $body = $article->getTranslation('body', $locale);
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <article>
        @if ($article->published_at)
            <time datetime="{{ $article->published_at->toDateString() }}"
                  class="text-xs uppercase tracking-wide text-safehouse-muted">
                {{ $article->published_at->locale($locale)->isoFormat('LL') }}
            </time>
        @endif

        <h1 class="mt-2 mb-8 text-3xl font-semibold tracking-tight md:text-4xl">{{ $title }}</h1>

        <div class="safehouse-glass safehouse-prose rounded-2xl p-8 md:p-10">
            {!! $body !!}
        </div>

        <p class="mt-8">
            <a href="{{ route('articles.index', ['locale' => $locale]) }}"
               class="text-sm font-medium text-safehouse-link transition hover:text-safehouse-link-hover">
                ← {{ __('site.pages.news_back') }}
            </a>
        </p>
    </article>
@endsection
