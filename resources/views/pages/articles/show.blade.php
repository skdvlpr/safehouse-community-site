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
    <article class="article-show">
        <a href="{{ route($indexRoute ?? 'articles.index', ['locale' => $locale]) }}"
           class="article-show__back safehouse-glass mb-6 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition">
            <span aria-hidden="true">←</span>
            {{ __($backLabel ?? 'site.pages.news_back') }}
        </a>

        <header class="article-show__header safehouse-glass mb-6 rounded-2xl p-6 md:p-8">
            <div class="mb-4 flex flex-wrap items-center gap-3">
                @if ($article->published_at)
                    <time datetime="{{ $article->published_at->toDateString() }}"
                          class="article-show__date">
                        {{ $article->published_at->locale($locale)->isoFormat('LL') }}
                    </time>
                @endif
                @if ($categoryName !== '')
                    <span class="news-meta-chip">{{ $categoryName }}</span>
                @endif
                @if ($article->show_author && $article->author && filled($article->author->first_name))
                    <span class="news-meta-chip">{{ __('site.pages.article_published_by', ['name' => $article->author->publicAuthorLabel()]) }}</span>
                @endif
            </div>

            <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">{{ $title }}</h1>
        </header>

        @include('pages.articles.partials.article-carousel', ['article' => $article, 'locale' => $locale])

        <div class="article-show__body safehouse-glass safehouse-prose rounded-2xl p-8 md:p-10">
            {!! \App\Support\CmsHtml::render($body) !!}
        </div>
    </article>
@endsection
