@php
    use App\Services\MeasurementBootService;
    use App\Services\PageService;

    $locale = app()->getLocale();
    $cookiePolicyUrl = app(PageService::class)->urlForKey('cookie', $locale);
    $privacyUrl = app(PageService::class)->urlForKey('privacy', $locale);
    $measurementBootable = app(MeasurementBootService::class)->isBootable();
@endphp

<div
    id="cookie-consent-banner"
    class="cookie-consent is-hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="cookie-consent-title"
    aria-hidden="true"
    data-store-url="{{ route('cookie-consent.store', ['locale' => $locale]) }}"
>
    <div class="cookie-consent__shell">
        <div class="cookie-consent__card">
            <button
                type="button"
                class="cookie-consent__dismiss"
                data-cookie-dismiss
                aria-label="{{ __('site.cookie.dismiss_aria') }}"
            >
                <span aria-hidden="true">×</span>
            </button>
            <div class="cookie-consent__main">
                <div class="cookie-consent__badge" aria-hidden="true">
                    <svg class="cookie-consent__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v.01M16 6v.01M8 6v.01M9 16h6M7.5 10.5a4.5 4.5 0 019 0c0 2.5-1.5 4-3 5.5-1.5-1.5-3-3-3-5.5Z" />
                    </svg>
                </div>

                <div class="cookie-consent__copy">
                    @include('layouts.partials.locale-switch', ['variant' => 'banner'])
                    <p id="cookie-consent-title" class="cookie-consent__title" data-banner-text data-text-it="{{ __('site.cookie.title', [], 'it') }}" data-text-en="{{ __('site.cookie.title', [], 'en') }}">{{ __('site.cookie.title') }}</p>
                    <p class="cookie-consent__text" data-banner-text data-text-it="{{ __($measurementBootable ? 'site.cookie.message_bootable' : 'site.cookie.message', [], 'it') }}" data-text-en="{{ __($measurementBootable ? 'site.cookie.message_bootable' : 'site.cookie.message', [], 'en') }}">{{ __($measurementBootable ? 'site.cookie.message_bootable' : 'site.cookie.message') }}</p>
                    @if ($cookiePolicyUrl || $privacyUrl)
                        <p class="cookie-consent__links">
                            @if ($cookiePolicyUrl)
                                <a href="{{ $cookiePolicyUrl }}">{{ __('site.nav.cookie') }}</a>
                            @endif
                            @if ($privacyUrl)
                                <span aria-hidden="true"> · </span>
                                <a href="{{ $privacyUrl }}">{{ __('site.nav.privacy') }}</a>
                            @endif
                        </p>
                    @endif
                </div>

                <div class="cookie-consent__actions">
                    <button type="button" class="cookie-consent__btn cookie-consent__btn--primary" data-cookie-accept-all>
                        {{ __('site.cookie.accept_all') }}
                    </button>
                    <button type="button" class="cookie-consent__btn cookie-consent__btn--secondary" data-cookie-essential-only>
                        {{ __('site.cookie.essential_only') }}
                    </button>
                    <button type="button" class="cookie-consent__btn cookie-consent__btn--ghost" data-cookie-open-preferences aria-expanded="false" aria-controls="cookie-consent-panel">
                        {{ __('site.cookie.manage') }}
                    </button>
                </div>
            </div>

            <div id="cookie-consent-panel" class="cookie-consent__panel is-hidden" aria-hidden="true">
                <div class="cookie-consent__panel-head">
                    <p class="cookie-consent__panel-title">{{ __('site.cookie.preferences_title') }}</p>
                    <button type="button" class="cookie-consent__btn cookie-consent__btn--ghost cookie-consent__btn--compact" data-cookie-close-preferences>
                        {{ __('site.cookie.close') }}
                    </button>
                </div>

                <div class="cookie-consent__options">
                    <label class="cookie-consent__option">
                        <input type="checkbox" checked disabled>
                        <span>
                            <strong>{{ __('site.cookie.essential_label') }}</strong>
                            <span class="cookie-consent__option-note">{{ __('site.cookie.essential_note') }}</span>
                        </span>
                    </label>
                    <label class="cookie-consent__option">
                        <input type="checkbox" data-cookie-analytics checked>
                        <span>
                            <strong>{{ __('site.cookie.analytics_label') }}</strong>
                            <span class="cookie-consent__option-note">{{ __($measurementBootable ? 'site.cookie.analytics_note_bootable' : 'site.cookie.analytics_note') }}</span>
                        </span>
                    </label>
                </div>

                <div class="cookie-consent__panel-actions">
                    <button type="button" class="cookie-consent__btn cookie-consent__btn--primary" data-cookie-save-preferences>
                        {{ __('site.cookie.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
