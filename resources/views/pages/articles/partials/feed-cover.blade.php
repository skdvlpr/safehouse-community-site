@php
    $slides = \App\Support\PageCarousel::slides($article->meta ?? null, $locale ?? app()->getLocale());
    $slideCount = count($slides);
@endphp

@if ($slideCount > 0)
    @php
        $showRoute = $showRoute ?? 'articles.show';
        $articleUrl = route($showRoute, [
            'locale' => $locale ?? app()->getLocale(),
            'articleSlug' => $article->getTranslation('slug', $locale ?? app()->getLocale(), false)
                ?: $article->getTranslation('slug', 'it'),
        ]);
        $cover = $slides[0];
    @endphp

    <a href="{{ $articleUrl }}" class="news-feed__cover" aria-hidden="true" tabindex="-1">
        <div class="news-feed__cover-wrap">
            <img src="{{ $cover['url'] }}"
                 alt="{{ $cover['alt'] }}"
                 class="news-feed__cover-image"
                 loading="lazy"
                 decoding="async">
            @if ($slideCount > 1)
                <span class="news-feed__cover-count">+{{ $slideCount - 1 }}</span>
            @endif
        </div>
    </a>
@endif
