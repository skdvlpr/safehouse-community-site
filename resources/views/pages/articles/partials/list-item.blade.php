@php
    $locale = $locale ?? app()->getLocale();
    $slug = $article->getTranslation('slug', $locale, false) ?: $article->getTranslation('slug', 'it');
    $articleTitle = $article->getTranslation('title', $locale);
    $articlesService = app(\App\Services\ArticleService::class);
    $category = $article->category;
    $categoryName = $category ? $articlesService->categoryName($category, $locale) : '';
    $cover = \App\Support\PageCarousel::firstSlide($article->meta ?? null, $locale);
@endphp

<article class="news-list__item">
    @if ($cover !== null)
        <a href="{{ route('articles.show', ['locale' => $locale, 'articleSlug' => $slug]) }}"
           class="news-list__thumb"
           aria-hidden="true"
           tabindex="-1">
            <img src="{{ $cover['url'] }}"
                 alt="{{ $cover['alt'] }}"
                 class="news-list__thumb-image"
                 loading="lazy"
                 decoding="async">
        </a>
    @endif
    <div class="news-list__main">
        @if ($article->published_at)
            <time datetime="{{ $article->published_at->toDateString() }}"
                  class="news-list__date">
                {{ $article->published_at->locale($locale)->isoFormat('LL') }}
            </time>
        @endif
        <h2 class="news-list__title">
            <a href="{{ route('articles.show', ['locale' => $locale, 'articleSlug' => $slug]) }}"
               class="news-list__link">
                {{ $articleTitle }}
            </a>
        </h2>
    </div>
    @if ($categoryName !== '')
        <span class="news-list__category">{{ $categoryName }}</span>
    @endif
</article>
