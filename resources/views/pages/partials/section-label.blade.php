@props([
    'page',
    'locale',
    'fallbackKey',
    'variant' => 'eyebrow',
])

@php
    $label = app(\App\Services\PageService::class)->sectionLabel($page, $locale, $fallbackKey);
@endphp

@if ($variant === 'band')
    <div class="page-section-band" aria-hidden="true">
        <span>{{ $label }}</span>
    </div>
@else
    <p class="template-eyebrow">{{ $label }}</p>
@endif
