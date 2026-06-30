@php
    $locale = app()->getLocale();
    $title = $page->getTranslation('title', $locale);
    $body = $page->getTranslation('body', $locale);
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    @include('pages.partials.page-header', ['title' => $title])

    <div class="safehouse-glass safehouse-prose mb-8 rounded-2xl p-8 md:p-10">
        {!! $body !!}
    </div>

    <a href="{{ route('articles.index', ['locale' => $locale]) }}"
       class="text-sm font-medium text-safehouse-link transition hover:text-safehouse-link-hover">
        {{ __('site.pages.news_index_cta') }}
    </a>
@endsection
