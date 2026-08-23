
@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-page-template-shell :page="$page">
        @include('pages.partials.section-label', [
            'page' => $page,
            'locale' => $locale,
            'fallbackKey' => 'site.pages.templates.default',
        ])

        @include('pages.partials.page-header', ['title' => $title, 'page' => $page])

        <article class="template-default-panel safehouse-glass safehouse-prose">
                {!! \App\Support\CmsHtml::render($body) !!}
        </article>
    </x-page-template-shell>
@endsection
