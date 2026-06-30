
@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-page-template-shell :page="$page">
        @include('pages.partials.template-eyebrow', ['label' => __('site.pages.templates.news_index')])

        @include('pages.partials.page-header', ['title' => $title, 'lead' => __('site.pages.news_lead'), 'page' => $page])

        <div class="safehouse-glass safehouse-prose mb-8 rounded-2xl p-8 md:p-10">
            {!! $body !!}
        </div>

        <div class="template-news-cta safehouse-glass">
            <div>
                <h2 class="text-xl font-semibold">{{ __('site.pages.news_title') }}</h2>
                <p class="mt-1 text-sm text-safehouse-muted">{{ __('site.pages.news_index_hint') }}</p>
            </div>
            <a href="{{ route('articles.index', ['locale' => $locale]) }}" class="safehouse-btn-primary">
                {{ __('site.pages.news_index_cta') }}
            </a>
        </div>

        @if (isset($recentArticles) && $recentArticles->isNotEmpty())
            <div class="template-news-preview">
                @foreach ($recentArticles as $article)
                    @php
                        $articleSlug = $article->getTranslation('slug', $locale, false) ?: $article->getTranslation('slug', 'it');
                        $articleTitle = $article->getTranslation('title', $locale);
                    @endphp
                    <a href="{{ route('articles.show', ['locale' => $locale, 'articleSlug' => $articleSlug]) }}"
                       class="template-news-preview__card transition hover:border-safehouse-primary/40 hover:text-safehouse-text">
                        <time class="text-xs uppercase tracking-wide text-safehouse-primary">
                            {{ $article->published_at?->locale($locale)->isoFormat('LL') }}
                        </time>
                        <p class="mt-2 font-medium text-safehouse-text">{{ $articleTitle }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </x-page-template-shell>
@endsection
