@php
    $content = app(\App\Services\SiteContentService::class);
    $title = $content->homeIndependenceTitle($locale ?? app()->getLocale());
    $body = $content->homeIndependenceBody($locale ?? app()->getLocale());
@endphp

@if ($title !== '' || $body !== '')
    <aside class="home-independence landing-reveal mb-4 md:mb-6" aria-label="{{ $title !== '' ? $title : __('site.home.independence.title') }}" data-reveal-from="up">
        <div class="home-independence__panel landing-reveal__target safehouse-glass">
            <p class="home-independence__text">
                @if ($title !== '')
                    <strong class="home-independence__title">{{ $title }}<span class="home-independence__colon">:</span></strong>
                @endif
                @if ($body !== '')
                    <span class="home-independence__body">{{ $body }}</span>
                @endif
            </p>
        </div>
    </aside>
@endif
