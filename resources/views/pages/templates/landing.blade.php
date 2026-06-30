@php
    $locale = app()->getLocale();
    $title = $page->getTranslation('title', $locale);
    $body = $page->getTranslation('body', $locale);
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <section class="safehouse-glass mb-10 rounded-2xl p-8 md:p-12 lg:p-16">
        <h1 class="mb-4 max-w-3xl text-3xl font-semibold tracking-tight md:text-5xl">{{ $title }}</h1>
        <div class="safehouse-prose max-w-2xl text-lg text-safehouse-muted">
            {!! $body !!}
        </div>
    </section>
@endsection
