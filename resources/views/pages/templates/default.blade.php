@php
    $locale = app()->getLocale();
    $title = $page->getTranslation('title', $locale);
    $body = $page->getTranslation('body', $locale);
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    @include('pages.partials.page-header', ['title' => $title])

    <article class="safehouse-glass safehouse-prose rounded-2xl p-8 md:p-10">
        {!! $body !!}
    </article>
@endsection
