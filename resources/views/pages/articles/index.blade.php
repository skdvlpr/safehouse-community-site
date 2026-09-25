@php
    $titleKey = $titleKey ?? 'site.pages.news_title';
    $leadKey = $leadKey ?? 'site.pages.news_lead';
    $emptyKey = $emptyKey ?? 'site.pages.news_empty';
    $emptyFilteredKey = $emptyFilteredKey ?? 'site.pages.news_empty_filtered';
@endphp

@extends('layouts.app')

@section('title', __($titleKey))

@section('content')
    <div class="motion-enter">
        @include('pages.partials.page-header', [
            'title' => __($titleKey),
            'lead' => __($leadKey),
            'prominent' => true,
            'align' => 'center',
        ])

        @include('pages.articles.partials.listing-toolbar', [
            'filters' => $filters,
            'categories' => $categories,
            'locale' => $locale,
            'indexRoute' => $indexRoute ?? 'articles.index',
            'filtersLabel' => $filtersLabel ?? 'site.pages.news_filters_label',
            'categoriesEmptyLabel' => $categoriesEmptyLabel ?? 'site.pages.news_categories_empty',
        ])

        @if ($articles->isEmpty())
            <div class="landing-reveal" data-reveal-from="up">
            <div class="landing-reveal__target safehouse-glass rounded-2xl p-8 text-center text-safehouse-muted">
                {{ $filters->hasActiveFilters() ? __($emptyFilteredKey) : __($emptyKey) }}
            </div>
            </div>
        @elseif ($filters->layout === 'list')
            <div class="news-list landing-reveal safehouse-glass" data-reveal-from="up">
                <div class="landing-reveal__target">
                @foreach ($articles as $article)
                    @include('pages.articles.partials.list-item', [
                        'article' => $article,
                        'locale' => $locale,
                        'showRoute' => $showRoute ?? 'articles.show',
                    ])
                @endforeach
                </div>
            </div>
        @else
            <div class="news-feed">
                @foreach ($articles as $article)
                    @include('pages.articles.partials.feed-item', [
                        'article' => $article,
                        'locale' => $locale,
                        'showRoute' => $showRoute ?? 'articles.show',
                    ])
                @endforeach
            </div>
        @endif

        @if ($articles->hasPages())
            <div class="news-pagination">
                {{ $articles->links() }}
            </div>
        @endif
    </div>
@endsection
