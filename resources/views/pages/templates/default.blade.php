
@extends('layouts.app')

@section('title', ($page->key ?? '') === 'faq' ? __('site.pages.faq_title') : $title)

@section('content')
    <x-page-template-shell :page="$page" class="motion-enter">
        @if (($page->key ?? '') === 'faq')
            @include('pages.partials.page-header', [
                'title' => __('site.pages.faq_title'),
                'lead' => __('site.pages.faq_tagline'),
                'prominent' => true,
            ])
        @else
            @include('pages.partials.section-label', [
                'page' => $page,
                'locale' => $locale,
                'fallbackKey' => 'site.pages.templates.default',
            ])

            @include('pages.partials.page-header', ['title' => $title, 'page' => $page])
        @endif

        <article class="template-default-panel safehouse-glass safehouse-prose">
                {!! \App\Support\CmsHtml::render($body) !!}
        </article>
    </x-page-template-shell>
@endsection
