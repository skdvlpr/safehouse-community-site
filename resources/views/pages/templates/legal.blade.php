@extends('layouts.app')

@php
    $legalKey = $page->key ?? '';
    $mergedLegal = in_array($legalKey, ['trasparenza', 'cookie', 'privacy'], true);
    $headingTitle = match ($legalKey) {
        'trasparenza' => __('site.pages.transparency_title'),
        'cookie' => __('site.pages.cookie_title'),
        'privacy' => __('site.pages.privacy_title'),
        default => $title,
    };
    $headingLead = match ($legalKey) {
        'cookie' => __('site.pages.cookie_tagline'),
        'privacy' => __('site.pages.privacy_tagline'),
        default => __('site.pages.legal_lead'),
    };
@endphp

@section('title', $mergedLegal ? $headingTitle : $title)

@section('content')
    <x-page-template-shell :page="$page" class="motion-enter">
        @if ($mergedLegal)
            @include('pages.partials.page-header', [
                'title' => $headingTitle,
                'lead' => $headingLead,
                'prominent' => true,
            ])
        @endif

        <div @class([
            'template-legal-column safehouse-glass',
            'template-legal-doc' => $mergedLegal,
            'template-legal-hero' => ! $mergedLegal,
        ])>
            @unless ($mergedLegal)
                @include('pages.partials.section-label', [
                    'page' => $page,
                    'locale' => $locale,
                    'fallbackKey' => 'site.pages.templates.legal',
                ])

                @include('pages.partials.page-header', ['title' => $title, 'lead' => __('site.pages.legal_lead'), 'page' => $page])
            @endunless

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

            @if ($mergedLegal)
                <div class="safehouse-prose mt-8">
                    {!! \App\Support\CmsHtml::render($body) !!}
                </div>
            @endif
        </div>

        @unless ($mergedLegal)
            <article class="template-legal-doc template-legal-column safehouse-glass safehouse-prose">
                {!! \App\Support\CmsHtml::render($body) !!}
            </article>
        @endunless
    </x-page-template-shell>
@endsection
