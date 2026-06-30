@extends('layouts.app')

@section('title', __('site.pages.news_title'))

@section('content')
    @include('pages.partials.page-header', [
        'title' => __('site.pages.news_title'),
        'lead' => __('site.pages.news_lead'),
    ])

    @if ($articles->isEmpty())
        <div class="safehouse-glass rounded-2xl p-8 text-center text-safehouse-muted">
            {{ __('site.pages.news_empty') }}
        </div>
    @else
        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($articles as $article)
                @php
                    $locale = app()->getLocale();
                    $slug = $article->getTranslation('slug', $locale, false) ?: $article->getTranslation('slug', 'it');
                    $articleTitle = $article->getTranslation('title', $locale);
                    $excerpt = $article->getTranslation('excerpt', $locale);
                @endphp
                <article class="safehouse-glass rounded-2xl p-6 md:p-8">
                    @if ($article->published_at)
                        <time datetime="{{ $article->published_at->toDateString() }}"
                              class="text-xs uppercase tracking-wide text-safehouse-muted">
                            {{ $article->published_at->locale($locale)->isoFormat('LL') }}
                        </time>
                    @endif
                    <h2 class="mt-2 text-lg font-semibold">
                        <a href="{{ route('articles.show', ['locale' => $locale, 'articleSlug' => $slug]) }}"
                           class="text-safehouse-text transition hover:text-safehouse-link">
                            {{ $articleTitle }}
                        </a>
                    </h2>
                    @if ($excerpt)
                        <p class="mt-2 text-sm text-safehouse-muted">{{ $excerpt }}</p>
                    @endif
                </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $articles->links() }}
        </div>
    @endif
@endsection
