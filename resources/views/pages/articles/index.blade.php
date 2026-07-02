@extends('layouts.app')

@section('title', __('site.pages.news_title'))

@section('content')
    @include('pages.partials.page-header', [
        'title' => __('site.pages.news_title'),
        'lead' => __('site.pages.news_lead'),
    ])

    @include('pages.articles.partials.listing-toolbar', [
        'filters' => $filters,
        'categories' => $categories,
        'locale' => $locale,
    ])

    @if ($articles->isEmpty())
        <div class="safehouse-glass rounded-2xl p-8 text-center text-safehouse-muted">
            {{ $filters->hasActiveFilters() ? __('site.pages.news_empty_filtered') : __('site.pages.news_empty') }}
        </div>
    @elseif ($filters->layout === 'list')
        <div class="news-list safehouse-glass">
            @foreach ($articles as $article)
                @include('pages.articles.partials.list-item', ['article' => $article, 'locale' => $locale])
            @endforeach
        </div>
    @else
        <div class="news-feed">
            @foreach ($articles as $article)
                @include('pages.articles.partials.feed-item', ['article' => $article, 'locale' => $locale])
            @endforeach
        </div>
    @endif

    @if ($articles->hasPages())
        <div class="news-pagination">
            {{ $articles->links() }}
        </div>
    @endif
@endsection
