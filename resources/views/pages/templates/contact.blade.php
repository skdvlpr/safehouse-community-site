@php
    $locale = app()->getLocale();
    $title = $page->getTranslation('title', $locale);
    $body = $page->getTranslation('body', $locale);
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    @include('pages.partials.page-header', ['title' => $title])

    <div class="grid gap-8 lg:grid-cols-2">
        <article class="safehouse-glass safehouse-prose rounded-2xl p-8 md:p-10">
            {!! $body !!}
        </article>

        <aside class="safehouse-glass rounded-2xl p-8 md:p-10">
            <h2 class="mb-4 text-lg font-semibold">{{ __('site.pages.contact_form_heading') }}</h2>
            <p class="text-sm text-safehouse-muted">{{ __('site.pages.contact_form_placeholder') }}</p>
        </aside>
    </div>
@endsection
