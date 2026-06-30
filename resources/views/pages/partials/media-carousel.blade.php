@props([
    'slides' => [],
])

@if (count($slides) > 0)
    <section
        class="page-carousel mb-8"
        data-page-carousel
        data-slides='@json($slides)'
        aria-roledescription="carousel"
        aria-label="{{ __('site.pages.carousel_label') }}"
    >
        <div class="page-carousel__stage">
            @if (count($slides) > 1)
                <button type="button" class="page-carousel__nav page-carousel__nav--prev" data-carousel-prev aria-label="{{ __('site.pages.carousel_prev') }}">
                    <span aria-hidden="true">‹</span>
                </button>
            @endif

            <div class="page-carousel__viewport">
                <img
                    src="{{ $slides[0]['url'] }}"
                    alt="{{ $slides[0]['alt'] }}"
                    class="page-carousel__image"
                    data-carousel-main
                    decoding="async"
                >
            </div>

            @if (count($slides) > 1)
                <button type="button" class="page-carousel__nav page-carousel__nav--next" data-carousel-next aria-label="{{ __('site.pages.carousel_next') }}">
                    <span aria-hidden="true">›</span>
                </button>
            @endif
        </div>

        @if (count($slides) > 1)
            <div class="page-carousel__thumbs" data-carousel-thumbs>
                @foreach ($slides as $index => $slide)
                    <button
                        type="button"
                        class="page-carousel__thumb @if ($index === 0) is-active @endif"
                        data-carousel-thumb
                        data-index="{{ $index }}"
                        aria-label="{{ __('site.pages.carousel_go_to', ['number' => $index + 1]) }}"
                    >
                        <img src="{{ $slide['url'] }}" alt="" class="page-carousel__thumb-image" loading="lazy" decoding="async">
                    </button>
                @endforeach
            </div>
        @endif
    </section>
@endif
