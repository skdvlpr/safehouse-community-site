@php
    $locale = app()->getLocale();
    $title = $page->getTranslation('title', $locale);
    $body = $page->getTranslation('body', $locale);
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    @include('pages.partials.page-header', ['title' => $title, 'lead' => __('site.pages.legal_lead')])

    <article class="safehouse-glass safehouse-prose rounded-2xl p-8 md:p-10 text-sm">
        {!! $body !!}
    </article>
@endsection
