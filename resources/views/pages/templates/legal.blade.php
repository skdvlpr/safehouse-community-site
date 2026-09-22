@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-page-template-shell :page="$page">
        <div class="template-legal-hero template-legal-column safehouse-glass">
            @include('pages.partials.section-label', [
                'page' => $page,
                'locale' => $locale,
                'fallbackKey' => 'site.pages.templates.legal',
            ])

            @include('pages.partials.page-header', ['title' => $title, 'lead' => __('site.pages.legal_lead'), 'page' => $page])

            <div class="template-legal-chips">
                <span class="template-legal-chip">{{ __('site.pages.legal_document') }}</span>
                <span class="template-legal-chip">{{ __('site.pages.legal_updated', ['date' => $page->updated_at?->locale($locale)->isoFormat('LL') ?? '—']) }}</span>
                <span class="template-legal-chip">C.F. 96629270586</span>
            </div>

            <p class="template-legal-locale">
                @include('layouts.partials.locale-switch', [
                    'variant' => 'text',
                    'linkClass' => 'template-legal-locale__link',
                ])
            </p>

            @include('layouts.partials.measurement-status')

            @if ($page->key === 'cookie')
                <div class="cookie-page-reopen">
                    <button type="button" class="cookie-consent__btn cookie-consent__btn--primary" data-cookie-reopen data-cookie-page-reopen>
                        {{ __('site.cookie.reopen_page') }}
                    </button>
                    <p class="cookie-page-reopen__note">{{ __('site.cookie.reopen_page_note') }}</p>
                </div>
            @endif
        </div>

        <article class="template-legal-doc template-legal-column safehouse-glass safehouse-prose">
            {!! \App\Support\CmsHtml::render($body) !!}
        </article>
    </x-page-template-shell>
@endsection
