@php
    $content = app(\App\Services\SiteContentService::class);
    $title = $content->homeIndependenceTitle($locale ?? app()->getLocale());
    $body = $content->homeIndependenceBody($locale ?? app()->getLocale());
@endphp

@if ($title !== '' || $body !== '')
    <aside class="home-independence mb-8 md:mb-10" aria-label="{{ $title !== '' ? $title : __('site.home.independence.title') }}">
        <div class="home-independence__panel safehouse-glass">
            <p class="home-independence__text">
                @if ($title !== '')
                    <strong class="home-independence__title">{{ $title }}:</strong>{{ $body !== '' ? ' ' : '' }}
                @endif
                @if ($body !== '')
                    <span class="home-independence__body">{{ $body }}</span>
                @endif
            </p>
        </div>
    </aside>
@endif
