
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

        @include('layouts.partials.measurement-status')

        @if ($page->key === 'cookie')
            <div class="cookie-page-reopen">
                <button type="button" class="cookie-consent__btn cookie-consent__btn--primary" data-cookie-reopen data-cookie-page-reopen>
                    {{ __('site.cookie.reopen_page') }}
                </button>
                <p class="cookie-page-reopen__note">{{ __('site.cookie.reopen_page_note') }}</p>
            </div>
        @endif

        <article class="template-legal-doc safehouse-glass safehouse-prose">
            {!! \App\Support\CmsHtml::render($body) !!}
        </article>
    </x-page-template-shell>
@endsection
