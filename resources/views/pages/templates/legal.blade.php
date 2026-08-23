
@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-page-template-shell :page="$page">
        @include('pages.partials.section-label', [
            'page' => $page,
            'locale' => $locale,
            'fallbackKey' => 'site.pages.templates.legal',
        ])

        @include('pages.partials.page-header', ['title' => $title, 'lead' => __('site.pages.legal_lead'), 'page' => $page])

        <div class="template-legal-meta">
            <span>{{ __('site.pages.legal_document') }}</span>
            <span aria-hidden="true">·</span>
            <span>{{ __('site.pages.legal_updated', ['date' => $page->updated_at?->locale($locale)->isoFormat('LL') ?? '—']) }}</span>
        </div>

        <article class="template-legal-doc safehouse-glass safehouse-prose">
            {!! \App\Support\CmsHtml::render($body) !!}
        </article>
    </x-page-template-shell>
@endsection
