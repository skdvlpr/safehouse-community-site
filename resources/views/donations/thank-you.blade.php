@extends('layouts.donation')

@php
    $locale = app()->getLocale();
    $campaignTitle = $campaign->getTranslation('title', $locale, false) ?: $campaign->getTranslation('title', 'it');
@endphp

@section('title', __('site.donations.thank_you_title'))

@section('content')
    <div class="rounded-3xl border border-white/10 bg-safehouse-modal p-8 text-center shadow-xl sm:p-10">
        <p class="mb-2 text-sm font-medium uppercase tracking-[0.18em] text-safehouse-primary">
            {{ $campaignTitle }}
        </p>

        <h1 class="mb-4 text-3xl font-semibold tracking-tight text-safehouse-text sm:text-4xl">
            {{ $thankYouHeading }}
        </h1>

        <p class="mx-auto max-w-lg whitespace-pre-line text-base leading-relaxed text-safehouse-muted">
            {{ $thankYouBody }}
        </p>

        @if (! empty($isRecurring))
            <aside class="safehouse-accent-panel mx-auto mt-8 max-w-xl rounded-2xl p-4 text-start sm:p-5" role="note">
                <h2 class="mb-2 text-sm font-semibold text-safehouse-primary">
                    {{ __('site.donations.thank_you_cancel_title') }}
                </h2>
                <p class="text-sm leading-relaxed text-safehouse-muted">
                    {{ __('site.donations.thank_you_cancel_body') }}
                </p>
                @include('donations.partials.stripe-customer-portal-link', [
                    'customerPortalLoginUrl' => $customerPortalLoginUrl ?? null,
                    'ctaLabel' => __('site.donations.thank_you_cancel_portal_cta'),
                ])
            </aside>
        @endif

        @if (! empty($paymentIntentId))
            <div class="mx-auto mt-8 max-w-xl rounded-2xl border border-white/10 bg-safehouse-page/80 p-4 text-start">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-safehouse-muted">
                    {{ __('site.donations.payment_reference') }}
                </p>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <code id="payment-reference" class="flex-1 break-all rounded-xl bg-black/30 px-3 py-2 text-sm text-safehouse-primary">
                        {{ $paymentIntentId }}
                    </code>
                    <button type="button"
                            id="copy-payment-reference"
                            class="safehouse-btn-secondary shrink-0 rounded-2xl px-4 py-2.5 text-sm">
                        {{ __('site.donations.copy_reference') }}
                    </button>
                </div>
                <p class="mt-3 text-xs text-safehouse-muted">
                    {{ __('site.donations.reference_hint') }}
                </p>
            </div>
        @endif

        <a href="{{ route('donations.index', ['locale' => $locale]) }}"
           class="safehouse-btn-primary mt-8 inline-flex rounded-2xl px-6 py-3">
            {{ __('Altre raccolte') }}
        </a>
    </div>
@endsection

@push('scripts')
@if (! empty($paymentIntentId))
<script>
(() => {
    const button = document.getElementById('copy-payment-reference');
    const reference = document.getElementById('payment-reference');
    if (! button || ! reference) {
        return;
    }

    const defaultLabel = button.textContent;
    const copiedLabel = @json(__('site.donations.reference_copied'));

    button.addEventListener('click', async () => {
        const text = reference.textContent?.trim() ?? '';
        if (text === '') {
            return;
        }

        try {
            await navigator.clipboard.writeText(text);
            button.textContent = copiedLabel;
            window.setTimeout(() => {
                button.textContent = defaultLabel;
            }, 2000);
        } catch (error) {
            window.prompt(@json(__('site.donations.copy_reference')), text);
        }
    });
})();
</script>
@endif
@endpush
