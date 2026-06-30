@extends('layouts.donation')

@php
    $locale = app()->getLocale();
    $title = $campaign->getTranslation('title', $locale, false) ?: $campaign->getTranslation('title', 'it');
    $formNotice = $campaign->getTranslation('form_notice', $locale, false) ?: $campaign->getTranslation('form_notice', 'it');
    $presets = $campaign->presetAmountCents();
@endphp

@section('title', $title)

@section('content')
    <h1 class="mb-2 text-3xl font-semibold">{{ $title }}</h1>
    @if ($campaign->getTranslation('description', $locale, false))
        <div class="prose prose-invert mb-6 max-w-none text-safehouse-muted">{!! nl2br(e($campaign->getTranslation('description', $locale))) !!}</div>
    @endif

    <form id="donation-form" class="space-y-6 rounded-xl border border-white/10 bg-safehouse-modal p-6">
        @csrf

        <div>
            <label class="mb-2 block text-sm font-medium">{{ __('Tipo di donatore') }}</label>
            <div class="flex gap-4">
                <label class="inline-flex items-center gap-2"><input type="radio" name="donor_type" value="individual" checked> {{ __('Persona fisica') }}</label>
                <label class="inline-flex items-center gap-2"><input type="radio" name="donor_type" value="organization"> {{ __('Organizzazione / azienda') }}</label>
            </div>
        </div>

        <div>
            <label for="donor_name" class="mb-2 block text-sm font-medium">{{ __('Nome o ragione sociale') }}</label>
            <input id="donor_name" name="donor_name" required maxlength="255"
                   class="w-full rounded-lg border border-white/10 bg-safehouse-page px-3 py-2">
        </div>

        <div>
            <label for="comment" class="mb-2 block text-sm font-medium">{{ __('Commento (opzionale)') }}</label>
            <textarea id="comment" name="comment" rows="3" maxlength="5000"
                      class="w-full rounded-lg border border-white/10 bg-safehouse-page px-3 py-2"></textarea>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium">{{ __('Importo') }} ({{ strtoupper($campaign->currency) }})</label>
            @if (count($presets) > 0)
                <div class="mb-3 flex flex-wrap gap-2" id="preset-buttons">
                    @foreach ($presets as $cents)
                        <button type="button" data-cents="{{ $cents }}"
                                class="preset-btn rounded-lg border border-white/10 px-4 py-2 text-sm hover:border-safehouse-primary">
                            {{ number_format($cents / 100, 0) }} €
                        </button>
                    @endforeach
                </div>
            @endif
            @if ($campaign->allow_custom_amount)
                <input id="amount_eur" name="amount_eur" type="number" min="{{ $campaign->min_amount_cents / 100 }}" step="0.01"
                       placeholder="{{ __('Importo personalizzato') }}"
                       class="w-full rounded-lg border border-white/10 bg-safehouse-page px-3 py-2">
            @endif
            <p class="mt-1 text-xs text-safehouse-muted">{{ __('Minimo') }}: {{ number_format($campaign->min_amount_cents / 100, 2) }} {{ strtoupper($campaign->currency) }}</p>
        </div>

        @if ($formNotice)
            <p class="rounded-lg bg-safehouse-page p-4 text-sm text-safehouse-muted">{{ $formNotice }}</p>
        @endif

        <p class="text-xs text-safehouse-muted">
            {{ __('I dati della carta non vengono mai memorizzati sui nostri server: il pagamento è gestito da Stripe.') }}
            <a href="{{ route('donations.privacy', ['locale' => $locale, 'donationCampaign' => $campaign->slug]) }}" class="text-safehouse-primary underline">{{ __('Informativa privacy pagamenti') }}</a>
        </p>

        <div id="payment-element" class="hidden rounded-lg border border-white/10 p-4"></div>
        <p id="payment-errors" class="hidden text-sm text-red-400"></p>

        <button type="submit" id="submit-button"
                class="safehouse-btn-primary w-full rounded-lg px-4 py-3 font-medium disabled:opacity-50">
            {{ __('Continua al pagamento') }}
        </button>
    </form>
@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
(() => {
    const form = document.getElementById('donation-form');
    const submitButton = document.getElementById('submit-button');
    const paymentElementContainer = document.getElementById('payment-element');
    const errorEl = document.getElementById('payment-errors');
    const intentUrl = @json(route('api.donations.intents.store', ['donationCampaign' => $campaign->slug]));
    const thankYouUrl = @json(route('donations.thank-you', ['locale' => $locale, 'donationCampaign' => $campaign->slug]));
    let stripe = null;
    let elements = null;
    let clientSecret = null;
    let selectedCents = null;

    document.querySelectorAll('.preset-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            selectedCents = parseInt(btn.dataset.cents, 10);
            document.getElementById('amount_eur')?.value && (document.getElementById('amount_eur').value = (selectedCents / 100).toFixed(2));
            document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('border-safehouse-primary'));
            btn.classList.add('border-safehouse-primary');
        });
    });

    function showError(message) {
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    }

    function resolveAmountCents() {
        if (selectedCents) return selectedCents;
        const input = document.getElementById('amount_eur');
        if (!input || !input.value) return null;
        return Math.round(parseFloat(input.value) * 100);
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

        if (!clientSecret) {
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
                    comment: form.comment.value || null,
                }),
            });

            const data = await response.json();
            if (!response.ok) {
                showError(data.message || @json(__('Impossibile avviare il pagamento.')));
                submitButton.disabled = false;
                return;
            }

            stripe = Stripe(data.publishable_key);
            clientSecret = data.client_secret;
            elements = stripe.elements({ clientSecret });
            const paymentElement = elements.create('payment');
            paymentElement.mount('#payment-element');
            paymentElementContainer.classList.remove('hidden');
            submitButton.textContent = @json(__('Paga ora'));
            submitButton.disabled = false;
            return;
        }

        const { error } = await stripe.confirmPayment({
            elements,
            confirmParams: { return_url: thankYouUrl },
        });

        if (error) {
            showError(error.message || @json(__('Pagamento non riuscito.')));
        }
        submitButton.disabled = false;
    });
})();
</script>
@endpush
