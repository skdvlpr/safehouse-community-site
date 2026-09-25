@extends('layouts.app')

@section('title', $title)

@section('content')
    @php
        $faqUrl = app(\App\Services\PageService::class)->urlForKey('faq', $locale);
        $contactBody = $body;
        if (is_string($contactBody)) {
            $contactBody = preg_replace(
                '#<a\b[^>]*href=(["\'])[^"\']*(?:domande-frequenti|frequently-asked)[^"\']*\1[^>]*>.*?</a>#is',
                '',
                $contactBody
            ) ?? $contactBody;
            $contactBody = preg_replace(
                '#https?://\S*(?:domande-frequenti|frequently-asked)\S*#i',
                '',
                $contactBody
            ) ?? $contactBody;
            $contactBody = preg_replace(
                '#<p>\s*(?:<strong>\s*)?(?:Domande frequenti|Frequently asked questions)\s*:?\s*(?:</strong>)?\s*(?:<br\s*/?>\s*)?</p>#iu',
                '',
                $contactBody
            ) ?? $contactBody;
        }
    @endphp

    <x-page-template-shell :page="$page" class="motion-enter">
        @include('pages.partials.page-header', [
            'title' => $title,
            'lead' => __('site.pages.contact_lead'),
            'prominent' => true,
            'align' => 'center',
        ])

        <div class="grid items-stretch gap-8 lg:grid-cols-2">
            <article class="template-contact-info safehouse-glass safehouse-prose">
                <div class="template-contact-intro">
                    {!! \App\Support\CmsHtml::render(is_string($contactBody) ? $contactBody : $body) !!}
                </div>

                <section class="template-contact-desks" aria-labelledby="contact-desks-heading">
                    <h2 id="contact-desks-heading" class="template-contact-desks__title">{{ __('site.pages.contact_desks_heading') }}</h2>
                    <ul class="template-contact-desks__list">
                        @foreach (Arr::wrap(__('site.pages.contact_desks_items')) as $desk)
                            @if (is_array($desk) && isset($desk['name'], $desk['hint']))
                                <li class="template-contact-desks__item">
                                    <span class="template-contact-desks__name">{{ $desk['name'] }}</span>
                                    <span class="template-contact-desks__hint">{{ $desk['hint'] }}</span>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </section>

                <dl class="template-contact-seat">
                    <div class="template-contact-seat__row">
                        <dt>{{ __('site.org.legal_seat_label') }}</dt>
                        <dd>{{ __('site.org.legal_seat') }}</dd>
                    </div>
                    <div class="template-contact-seat__row">
                        <dt>{{ __('site.org.runts_label') }}</dt>
                        <dd>{{ __('site.org.runts_value') }}</dd>
                    </div>
                    <div class="template-contact-seat__row">
                        <dt>{{ __('site.org.fiscal_code_label') }}</dt>
                        <dd>{{ __('site.org.fiscal_code_value') }}</dd>
                    </div>
                    <div class="template-contact-seat__row">
                        <dt>{{ __('site.org.operative_seat_label') }}</dt>
                        <dd>{{ __('site.org.operative_seat') }}</dd>
                    </div>
                </dl>

                <div class="template-contact-faq">
                    <p class="template-contact-faq__lead">{{ __('site.pages.contact_faq_lead') }}</p>
                    @if ($faqUrl)
                        <p>
                            <a href="{{ $faqUrl }}" class="safehouse-btn-primary template-contact-faq__btn">{{ __('site.pages.contact_faq') }}</a>
                        </p>
                    @endif
                </div>
            </article>

            <aside class="template-contact-aside safehouse-glass">
                <h2 class="mb-4 text-lg font-semibold">{{ __('site.pages.contact_form_heading') }}</h2>
                @include('pages.partials.contact-form-shell')
            </aside>
        </div>
    </x-page-template-shell>
@endsection

@if (app(\App\Services\TurnstileVerifier::class)->enabled())
    @push('scripts')
        <script>
            (function () {
                var theme = document.documentElement.getAttribute('data-theme');
                if (theme !== 'light' && theme !== 'dark') {
                    theme = 'dark';
                }
                document.querySelectorAll('.cf-turnstile').forEach(function (el) {
                    el.setAttribute('data-theme', theme);
                });
            })();
        </script>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endpush
@endif
