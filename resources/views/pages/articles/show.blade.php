@php
    $locale = app()->getLocale();
    $title = $article->getTranslation('title', $locale);
    $body = $article->getTranslation('body', $locale);
    $articlesService = app(\App\Services\ArticleService::class);
    $category = $article->category;
    $categoryName = $category ? $articlesService->categoryName($category, $locale) : '';
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <article>
        <div class="mb-2 flex flex-wrap items-center gap-3">
            @if ($article->published_at)
                <time datetime="{{ $article->published_at->toDateString() }}"
                      class="text-xs uppercase tracking-wide text-safehouse-muted">
                    {{ $article->published_at->locale($locale)->isoFormat('LL') }}
                </time>
            @endif
            @if ($categoryName !== '')
                <span class="rounded-full border border-white/10 px-2.5 py-0.5 text-xs font-medium text-safehouse-muted">
                    {{ $categoryName }}
                </span>
            @endif
        </div>

        <h1 class="mb-8 text-3xl font-semibold tracking-tight md:text-4xl">{{ $title }}</h1>

        @include('pages.articles.partials.article-carousel', ['article' => $article, 'locale' => $locale])

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
