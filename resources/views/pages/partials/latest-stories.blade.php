@if (($latestStories ?? []) !== [])
    <section class="story-slider mb-4 md:mb-6" aria-label="{{ __('site.nav.news') }}" data-story-slider>
        <div class="story-slider__bar">
            <button type="button" class="story-slider__arrow" data-story-prev aria-label="{{ __('site.pages.news_all') }}">‹</button>
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
            <button type="button" class="story-slider__arrow" data-story-next aria-label="{{ __('site.pages.editorial_all') }}">›</button>
        </div>
    </section>
    <script>
        (function () {
            var root = document.currentScript.previousElementSibling;
            if (!root || !root.hasAttribute('data-story-slider')) {
                return;
            }
            var track = root.querySelector('[data-story-track]');
            var index = 0;
            var timer = null;
            function step() {
                var cards = track.children;
                if (cards.length < 2) {
                    return;
                }
                var card = cards[0];
                var gap = 16;
                var width = card.getBoundingClientRect().width + gap;
                index = (index + 1) % cards.length;
                track.style.transform = 'translateX(' + (-index * width) + 'px)';
            }
            function start() {
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    return;
                }
                timer = window.setInterval(step, 5000);
            }
            root.querySelector('[data-story-next]').addEventListener('click', function () {
                window.clearInterval(timer);
                step();
            });
            root.querySelector('[data-story-prev]').addEventListener('click', function () {
                window.clearInterval(timer);
                var cards = track.children;
                if (cards.length < 2) {
                    return;
                }
                var card = cards[0];
                var gap = 16;
                var width = card.getBoundingClientRect().width + gap;
                index = (index - 1 + cards.length) % cards.length;
                track.style.transform = 'translateX(' + (-index * width) + 'px)';
            });
            start();
        })();
    </script>
@endif
