@props([
    'page',
    'template' => null,
])

@php
    $templateKey = $template ?? ($page->template ?: 'default');
@endphp

<div {{ $attributes->class(['template-page', "template-page--{$templateKey}"]) }} data-page-template="{{ $templateKey }}">
    {{ $slot }}
</div>
