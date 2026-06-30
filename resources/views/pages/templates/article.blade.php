@php
    $locale = app()->getLocale();
    $title = $page->getTranslation('title', $locale);
    $body = $page->getTranslation('body', $locale);
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    @include('pages.partials.page-header', ['title' => $title])

    <article class="safehouse-glass safehouse-prose mx-auto max-w-3xl rounded-2xl p-8 md:p-12">
        {!! $body !!}
    </article>
@endsection
