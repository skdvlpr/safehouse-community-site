@php
    $locale = $locale ?? app()->getLocale();
    $codice = app(\App\Services\DonationSettingsService::class)->codiceFiscale();
    $href = route('donations.five-per-mille', ['locale' => $locale]);
@endphp
<div class="site-five five-banner">
    <div class="site-content site-five__inner">
        <a class="site-five__link" href="{{ $href }}">
            <span class="site-five__mark" aria-hidden="true">5×</span>
            <span class="site-five__text">
                <span class="site-five__brand">{{ __('site.discovery.five_per_mille_title', [], $locale) }}</span>
                <span class="site-five__sep" aria-hidden="true">|</span>
                <span>{{ __('site.discovery.five_banner_line', [], $locale) }}</span>
                @if ($codice !== '')
                    <span class="site-five__sep" aria-hidden="true">|</span>
                    <span>C.F. {{ $codice }}</span>
                @endif
            </span>
        </a>
        @if ($codice !== '')
            <button type="button" class="site-five__copy" data-copy-codice="{{ $codice }}">
                {{ __('site.donations.copy_codice_fiscale', [], $locale) }}
            </button>
        @endif
    </div>
</div>
<script>
    document.querySelectorAll('[data-copy-codice]').forEach(function (button) {
        if (button.dataset.copyBound === '1') {
            return;
        }
        button.dataset.copyBound = '1';
        button.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            var value = button.getAttribute('data-copy-codice') || '';
            if (navigator.clipboard && value !== '') {
                navigator.clipboard.writeText(value);
            }
        });
    });
</script>
