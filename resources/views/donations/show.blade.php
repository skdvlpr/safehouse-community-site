@extends('layouts.donation')

@php
    $locale = app()->getLocale();
    $title = $campaign->getTranslation('title', $locale, false) ?: $campaign->getTranslation('title', 'it');
    $formNotice = $campaign->getTranslation('form_notice', $locale, false) ?: $campaign->getTranslation('form_notice', 'it');
    $description = $campaign->getTranslation('description', $locale, false);
    $presets = $campaign->presetAmountCents();
@endphp

@section('title', $title)

@section('content')
    @if (session('donation_notice'))
        <p class="mb-4 rounded-2xl border border-amber-500/30 bg-amber-500/10 p-4 text-sm text-amber-100">
            {{ session('donation_notice') }}
        </p>
    @endif

    <form id="donation-form" class="space-y-6 rounded-3xl border border-white/10 bg-safehouse-modal p-6 shadow-xl sm:p-8">
        <header class="space-y-3">
            <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">{{ $title }}</h1>
            @if (! empty($fundraisingProgress))
                @include('donations.partials.fundraising-progress', ['progress' => $fundraisingProgress])
            @endif
            @if ($description)
                <div class="safehouse-prose max-w-none text-base text-safehouse-muted [&_p:last-child]:mb-0">
                    {!! $description !!}
                </div>
            @endif
        </header>

        @csrf

        <div class="space-y-3">
            <span class="block text-sm font-medium">{{ __('Tipo di donatore') }}</span>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <label class="flex cursor-pointer items-center justify-center rounded-2xl border border-white/10 bg-safehouse-page/70 px-4 py-3 text-sm has-[:checked]:border-safehouse-primary has-[:checked]:bg-safehouse-primary/10">
                    <input type="radio" name="donor_type" value="individual" class="sr-only" checked>
                    <span>{{ __('Persona fisica') }}</span>
                </label>
                <label class="flex cursor-pointer items-center justify-center rounded-2xl border border-white/10 bg-safehouse-page/70 px-4 py-3 text-sm has-[:checked]:border-safehouse-primary has-[:checked]:bg-safehouse-primary/10">
                    <input type="radio" name="donor_type" value="organization" class="sr-only">
                    <span>{{ __('Organizzazione / azienda') }}</span>
                </label>
            </div>
        </div>

        <div class="space-y-2">
            <label for="donor_name" class="block text-sm font-medium">{{ __('Nome o ragione sociale') }}</label>
            <input id="donor_name" name="donor_name" required maxlength="255"
                   class="w-full rounded-2xl border border-white/10 bg-safehouse-page px-4 py-3 outline-none transition focus:border-safehouse-primary/60 focus:ring-2 focus:ring-safehouse-primary/20">
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <label for="donor_email" class="block text-sm font-medium">{{ __('Email') }}</label>
                <input id="donor_email" name="donor_email" type="email" maxlength="255" autocomplete="email"
                       class="w-full rounded-2xl border border-white/10 bg-safehouse-page px-4 py-3 outline-none transition focus:border-safehouse-primary/60 focus:ring-2 focus:ring-safehouse-primary/20">
            </div>
            <div class="space-y-2 donation-phone-field">
                <label for="donor_phone" class="block text-sm font-medium">{{ __('Telefono') }}</label>
                <input id="donor_phone" name="donor_phone" type="tel" maxlength="50" autocomplete="tel"
                       class="w-full rounded-2xl border border-white/10 bg-safehouse-page px-4 py-3 outline-none transition focus:border-safehouse-primary/60 focus:ring-2 focus:ring-safehouse-primary/20">
                <input type="hidden" id="donor_phone_country" name="donor_phone_country" value="">
            </div>
        </div>
        <p class="text-xs text-safehouse-muted">{{ __('Inserisci almeno un\'email o un numero di telefono per collegare la donazione al CRM.') }}</p>

        <div class="space-y-2">
            <label for="comment" class="block text-sm font-medium">{{ __('Commento (opzionale)') }}</label>
            <textarea id="comment" name="comment" rows="3" maxlength="5000"
                      class="min-h-28 w-full resize-y rounded-2xl border border-white/10 bg-safehouse-page px-4 py-3 outline-none transition focus:border-safehouse-primary/60 focus:ring-2 focus:ring-safehouse-primary/20"></textarea>
        </div>

        <div class="space-y-3">
            <span class="block text-sm font-medium">{{ __('Importo') }} ({{ strtoupper($campaign->currency) }})</span>

            @if (count($presets) > 0)
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3" id="preset-buttons">
                    @foreach ($presets as $cents)
                        <button type="button"
                                data-cents="{{ $cents }}"
                                class="preset-btn rounded-2xl border border-white/10 bg-safehouse-page/70 px-3 py-3 text-sm font-semibold transition hover:border-safehouse-primary/40">
                            {{ $campaign->formatPresetLabel($cents) }}
                        </button>
                    @endforeach
                </div>
            @endif

            @if ($campaign->allow_custom_amount)
                <div class="relative">
                    <input id="amount_eur"
                           name="amount_eur"
                           type="number"
                           min="{{ number_format($campaign->min_amount_cents / 100, 2, '.', '') }}"
                           step="0.01"
                           inputmode="decimal"
                           aria-label="{{ __('Importo personalizzato') }}"
                           placeholder="0,00"
                           class="w-full rounded-2xl border border-white/10 bg-safehouse-page py-3 pe-16 ps-4 outline-none transition focus:border-safehouse-primary/60 focus:ring-2 focus:ring-safehouse-primary/20">
                    <span class="pointer-events-none absolute inset-y-0 end-4 flex items-center text-sm font-medium text-safehouse-muted">
                        {{ strtoupper($campaign->currency) }}
                    </span>
                </div>
            @endif

            <p class="text-xs text-safehouse-muted">
                {{ __('Minimo') }}: {{ $campaign->formatPresetLabel($campaign->min_amount_cents) }}
            </p>
        </div>

        @if ($formNotice)
            <p class="rounded-2xl border border-white/10 bg-safehouse-page/70 p-4 text-sm text-safehouse-muted">{{ $formNotice }}</p>
        @endif

        <p class="text-xs leading-relaxed text-safehouse-muted">
            {{ __('I dati della carta non vengono mai memorizzati sui nostri server: il pagamento è gestito da Stripe.') }}
            <a href="{{ route('donations.privacy', ['locale' => $locale, 'campaignSlug' => $campaign->slug]) }}"
               class="text-safehouse-link underline underline-offset-2 hover:text-safehouse-link-hover">
                {{ __('Informativa privacy pagamenti') }}
            </a>
        </p>

        @if ($stripeMock && config('app.debug'))
            <p class="rounded-2xl border border-amber-500/30 bg-amber-500/10 p-4 text-sm text-amber-100">
                {{ __('site.donations.dev_simulation') }}
            </p>
        @endif

        <div id="payment-element" class="hidden rounded-2xl border border-white/10 bg-safehouse-page/80 p-4"></div>

        <button type="button" id="apply-donor-to-stripe"
                class="hidden w-full rounded-2xl border border-white/15 bg-safehouse-page/70 px-4 py-3 text-sm font-medium transition hover:border-safehouse-primary/40">
            {{ __('Usa i miei dati nel modulo di pagamento') }}
        </button>

        @if ($stripeMock && config('app.debug'))
            <div id="mock-payment-panel" class="hidden rounded-2xl border border-dashed border-amber-500/40 p-4 text-sm text-amber-100">
                {{ __('site.donations.dev_simulation_hint') }}
            </div>
        @endif

        <p id="payment-errors" class="hidden rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200"></p>

        <button type="submit" id="submit-button"
                class="safehouse-btn-primary w-full rounded-2xl px-4 py-3.5 text-base font-semibold disabled:opacity-50">
            {{ __('Continua al pagamento') }}
        </button>
    </form>
@endsection

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@24.6.0/build/css/intlTelInput.css">
@endpush

@push('scripts')
@if (! $stripeMock)
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24.6.0/build/js/intlTelInput.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24.6.0/build/js/utils.js"></script>
<script src="https://js.stripe.com/v3/"></script>
@endif
<script>
(() => {
    const form = document.getElementById('donation-form');
    const submitButton = document.getElementById('submit-button');
    const paymentElementContainer = document.getElementById('payment-element');
    const mockPaymentPanel = document.getElementById('mock-payment-panel');
    const errorEl = document.getElementById('payment-errors');
    const amountInput = document.getElementById('amount_eur');
    const applyDonorToStripeButton = document.getElementById('apply-donor-to-stripe');
    const intentUrl = @json(route('api.donations.intents.store', ['donationCampaign' => $campaign->slug]));
    const thankYouBaseUrl = @json(route('donations.thank-you', ['locale' => $locale, 'campaignSlug' => $campaign->slug]));
    const stripeMock = @json($stripeMock);
    let stripe = null;
    let elements = null;
    let paymentElement = null;
    let clientSecret = null;
    let completeUrl = null;
    let selectedCents = null;
    let donorPhoneInput = null;
    const donorPhoneCountryInput = document.getElementById('donor_phone_country');
    const donorPhoneElement = document.getElementById('donor_phone');

    if (donorPhoneElement && window.intlTelInput) {
        donorPhoneInput = window.intlTelInput(donorPhoneElement, {
            initialCountry: 'it',
            preferredCountries: ['it', 'ru', 'us', 'gb', 'de', 'fr'],
            separateDialCode: true,
            nationalMode: false,
            formatOnDisplay: true,
            autoPlaceholder: 'aggressive',
        });

        const syncPhoneCountry = () => {
            const country = donorPhoneInput.getSelectedCountryData();
            if (donorPhoneCountryInput && country?.dialCode) {
                donorPhoneCountryInput.value = country.dialCode;
            }
        };

        donorPhoneElement.addEventListener('countrychange', syncPhoneCountry);
        donorPhoneElement.addEventListener('input', () => {
            const raw = donorPhoneElement.value.trim();
            if (raw.startsWith('+')) {
                donorPhoneInput.setNumber(raw);
            }
        });

        syncPhoneCountry();
    }

    function donorEmail() {
        return form.donor_email.value.trim();
    }

    function donorPhone() {
        if (!donorPhoneInput) {
            return donorPhoneElement?.value.trim() ?? '';
        }

        const raw = donorPhoneElement.value.trim();
        if (raw === '') {
            return '';
        }

        if (donorPhoneInput.isValidNumber()) {
            return donorPhoneInput.getNumber();
        }

        if (raw.startsWith('+')) {
            return raw.replace(/[\s\-\(\)]+/g, '');
        }

        return raw;
    }

    function hasDonorContactChannel() {
        return donorEmail() !== '' || donorPhone() !== '';
    }

    function donorBillingDetails() {
        const details = {
            name: form.donor_name.value.trim(),
        };

        const email = donorEmail();
        const phone = donorPhone();

        if (email !== '') {
            details.email = email;
        }

        if (phone !== '') {
            details.phone = phone;
        }

        return details;
    }

    function applyDonorDetailsToStripe() {
        if (!paymentElement) {
            return;
        }

        paymentElement.update({
            defaultValues: {
                billingDetails: donorBillingDetails(),
            },
        });
    }

    applyDonorToStripeButton?.addEventListener('click', () => {
        applyDonorDetailsToStripe();
    });

    function thankYouUrl() {
        const params = new URLSearchParams();
        const donorName = form.donor_name.value.trim();
        if (donorName !== '') {
            params.set('donor_name', donorName);
        }

        const query = params.toString();
        return query === '' ? thankYouBaseUrl : `${thankYouBaseUrl}?${query}`;
    }

    function clearPresetSelection() {
        selectedCents = null;
        document.querySelectorAll('.preset-btn').forEach((button) => {
            button.classList.remove('border-safehouse-primary', 'bg-safehouse-primary/15', 'text-white');
        });
    }

    function selectPreset(button) {
        selectedCents = parseInt(button.dataset.cents, 10);
        document.querySelectorAll('.preset-btn').forEach((item) => {
            item.classList.remove('border-safehouse-primary', 'bg-safehouse-primary/15', 'text-white');
        });
        button.classList.add('border-safehouse-primary', 'bg-safehouse-primary/15', 'text-white');

        if (amountInput) {
            amountInput.value = (selectedCents / 100).toFixed(2);
        }
    }

    document.querySelectorAll('.preset-btn').forEach((button) => {
        button.addEventListener('click', () => selectPreset(button));
    });

    amountInput?.addEventListener('input', () => clearPresetSelection());

    function showError(message) {
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    }

    function resolveAmountCents() {
        if (selectedCents) {
            return selectedCents;
        }

        if (!amountInput || amountInput.value === '') {
            return null;
        }

        return Math.round(parseFloat(amountInput.value.replace(',', '.')) * 100);
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        errorEl.classList.add('hidden');
        submitButton.disabled = true;

        const amountCents = resolveAmountCents();
        if (!amountCents) {
            showError(@json(__('Seleziona o inserisci un importo.')));
            submitButton.disabled = false;
            return;
        }

        if (!hasDonorContactChannel()) {
            showError(@json(__('Inserisci un\'email o un numero di telefono.')));
            submitButton.disabled = false;
            return;
        }

        const phoneValue = donorPhone();
        if (donorPhoneElement?.value.trim() !== '' && donorPhoneInput && !donorPhoneInput.isValidNumber()) {
            showError(@json(__('Inserisci un numero di telefono valido con prefisso internazionale.')));
            submitButton.disabled = false;
            return;
        }

        if (!clientSecret && !completeUrl) {
            const response = await fetch(intentUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
                },
                body: JSON.stringify({
                    amount_cents: amountCents,
                    donor_name: form.donor_name.value,
                    donor_type: form.querySelector('input[name=donor_type]:checked').value,
                    donor_email: donorEmail() || null,
                    donor_phone: phoneValue || null,
                    donor_phone_country: donorPhoneCountryInput?.value || null,
                    comment: form.comment.value || null,
                }),
            });

            const data = await response.json();
            if (!response.ok) {
                showError(data.message || @json(__('Impossibile avviare il pagamento.')));
                submitButton.disabled = false;
                return;
            }

            if (data.mock && data.complete_url) {
                completeUrl = data.complete_url;
                mockPaymentPanel?.classList.remove('hidden');
                submitButton.textContent = @json(__('Simula pagamento riuscito'));
                submitButton.disabled = false;
                return;
            }

            stripe = Stripe(data.publishable_key);
            clientSecret = data.client_secret;
            elements = stripe.elements({ clientSecret });
            paymentElement = elements.create('payment', {
                defaultValues: {
                    billingDetails: donorBillingDetails(),
                },
            });
            paymentElement.mount('#payment-element');
            paymentElement.on('ready', () => {
                applyDonorDetailsToStripe();
                applyDonorToStripeButton?.classList.remove('hidden');
            });
            paymentElementContainer.classList.remove('hidden');
            submitButton.textContent = @json(__('Paga ora'));
            submitButton.disabled = false;
            return;
        }

        if (stripeMock && completeUrl) {
            const completeResponse = await fetch(completeUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
                },
            });
            const completeData = await completeResponse.json();
            if (!completeResponse.ok) {
                showError(completeData.message || @json(__('Registrazione donazione non riuscita.')));
                submitButton.disabled = false;
                return;
            }

            const params = new URLSearchParams();
            params.set('payment_intent', completeData.payment_intent_id ?? '');
            const donorName = form.donor_name.value.trim();
            if (donorName !== '') {
                params.set('donor_name', donorName);
            }
            window.location.href = `${thankYouBaseUrl}?${params.toString()}`;
            return;
        }

        const { error } = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: thankYouUrl(),
                payment_method_data: {
                    billing_details: donorBillingDetails(),
                },
            },
        });

        if (error) {
            showError(error.message || @json(__('Pagamento non riuscito.')));
        }
        submitButton.disabled = false;
    });
})();
</script>
@endpush
