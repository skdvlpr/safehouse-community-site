@php
    $locale = $locale ?? app()->getLocale();
    $showRoute = $showRoute ?? 'articles.show';
    $slug = $article->getTranslation('slug', $locale, false) ?: $article->getTranslation('slug', 'it');
    $articleTitle = $article->getTranslation('title', $locale);
    $excerpt = $article->getTranslation('excerpt', $locale);
    $articlesService = app(\App\Services\ArticleService::class);
    $category = $article->category;
    $categoryName = $category ? $articlesService->categoryName($category, $locale) : '';
@endphp

<article class="news-feed__item safehouse-glass">
    <div class="news-feed__meta">
        @if ($article->published_at)
            <time datetime="{{ $article->published_at->toDateString() }}"
                  class="news-feed__date">
                {{ $article->published_at->locale($locale)->isoFormat('LL') }}
            </time>
        @endif
        @if ($categoryName !== '')
            <span class="news-meta-chip">{{ $categoryName }}</span>
        @endif
    </div>

    @include('pages.articles.partials.feed-cover', [
        'article' => $article,
        'locale' => $locale,
        'showRoute' => $showRoute,
    ])

    <h2 class="news-feed__title">
        <a href="{{ route($showRoute, ['locale' => $locale, 'articleSlug' => $slug]) }}"
           class="news-feed__link">
            {{ $articleTitle }}
        </a>
    </h2>

    @if ($excerpt)
        <p class="news-feed__excerpt">{{ $excerpt }}</p>
    @endif
</article>
