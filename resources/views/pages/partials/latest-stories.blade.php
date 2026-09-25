@if (($latestStories ?? []) !== [])
    <section class="story-slider mb-4 md:mb-6" aria-label="{{ __('site.nav.news') }}" data-story-slider>
        <div class="story-slider__bar">
            <button type="button" class="story-slider__arrow" data-story-prev aria-label="{{ __('site.pages.story_prev') }}">‹</button>
            <div class="story-slider__viewport">
                <div class="story-slider__track" data-story-track>
                    @foreach ($latestStories as $story)
                        <a class="story-card safehouse-glass" href="{{ $story['url'] }}">
                            @if (! empty($story['image']))
                                <img class="story-card__thumb" src="{{ $story['image'] }}" alt="">
                            @endif
                            <span class="story-card__body">
                                <span class="template-eyebrow">{{ $story['kind'] }}</span>
                                @if (($story['category'] ?? '') !== '')
                                    <span class="news-meta-chip">{{ $story['category'] }}</span>
                                @endif
                                <span class="story-card__title">{{ $story['title'] }}</span>
                                @if (($story['excerpt'] ?? '') !== '')
                                    <span class="story-card__excerpt">{{ $story['excerpt'] }}</span>
                                @endif
                            </span>
                        </a>
                    @endforeach
                </div>
                <p class="story-slider__links">
                    <a class="safehouse-btn-secondary" href="{{ route('articles.index', ['locale' => $locale]) }}">{{ __('site.pages.news_all') }}</a>
                    <a class="safehouse-btn-secondary" href="{{ route('editorial-articles.index', ['locale' => $locale]) }}">{{ __('site.pages.editorial_all') }}</a>
                </p>
            </div>
            <button type="button" class="story-slider__arrow" data-story-next aria-label="{{ __('site.pages.story_next') }}">›</button>
        </div>
    </section>
@endif
