@php
    $pageModel = $page ?? null;
    $backgroundUrl = app(\App\Services\SiteAppearanceSettings::class)
        ->backgroundUrlForPage($pageModel instanceof \App\Models\Page ? $pageModel : null);
@endphp
@if (is_string($backgroundUrl) && $backgroundUrl !== '')
    <style>
        :root,
        html[data-theme='dark'],
        html[data-theme='light'] {
            --safehouse-bg-image: url('{{ $backgroundUrl }}');
        }
    </style>
@endif
