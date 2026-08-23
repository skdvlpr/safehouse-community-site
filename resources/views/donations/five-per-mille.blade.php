@extends('layouts.donation')

@section('title', $donationSettings->localized($donationSettings->fivePerMille(), 'heading', $locale))

@section('content')
    @php
        $five = $donationSettings->fivePerMille();
        $codice = $donationSettings->codiceFiscale();
    @endphp

    <p class="mb-6">
        <a href="{{ route('donations.index', ['locale' => $locale]) }}" class="text-sm text-safehouse-muted hover:text-safehouse-primary">
            ← {{ __('site.donations.five_per_mille_back') }}
        </a>
    </p>

    <article class="donation-five-per-mille safehouse-glass rounded-2xl p-8 md:p-12">
        <p class="mb-3 text-sm font-medium uppercase tracking-wider text-safehouse-primary">
            {{ $donationSettings->localized($five, 'menu_label', $locale) }}
        </p>

        <h1 class="donation-five-per-mille__title mb-4 text-4xl font-semibold tracking-tight md:text-5xl lg:text-6xl">
            {{ $donationSettings->localized($five, 'heading', $locale) }}
        </h1>

        @if ($donationSettings->localized($five, 'lead', $locale) !== '')
            <p class="mb-8 max-w-2xl text-lg text-safehouse-muted md:text-xl">
                {{ $donationSettings->localized($five, 'lead', $locale) }}
            </p>
        @endif

        @if ($donationSettings->localized($five, 'body', $locale) !== '')
            <div class="safehouse-prose mb-10 max-w-none">
                {!! $donationSettings->localized($five, 'body', $locale) !!}
            </div>
        @endif

        @if ($codice !== '')
            <section class="donation-five-per-mille__codice safehouse-accent-panel mb-10 rounded-xl p-6 md:p-8" aria-labelledby="codice-fiscale-heading">
                <h2 id="codice-fiscale-heading" class="mb-3 text-sm font-semibold uppercase tracking-wide text-safehouse-primary">
                    {{ $donationSettings->localized($five, 'codice_label', $locale) }}
                </h2>
                <p class="donation-five-per-mille__codice-value mb-4 font-mono text-3xl font-semibold tracking-wider text-safehouse-text md:text-4xl" data-copy-value="{{ $codice }}">
                    {{ $codice }}
                </p>
                <button type="button" class="safehouse-btn-secondary" data-copy-target="codice" data-copy-label="{{ __('site.donations.copy_codice_fiscale') }}" data-copied-label="{{ __('site.donations.copied') }}">
                    {{ __('site.donations.copy_codice_fiscale') }}
                </button>
            </section>
        @endif

        @if ($donationSettings->localized($five, 'instructions', $locale) !== '')
            <section class="safehouse-prose max-w-none" aria-labelledby="five-per-mille-instructions">
                <h2 id="five-per-mille-instructions" class="mb-4 text-xl font-semibold not-prose">
                    {{ __('site.donations.five_per_mille_instructions_heading', [], $locale) }}
                </h2>
                {!! $donationSettings->localized($five, 'instructions', $locale) !!}
            </section>
        @endif
    </article>

    @push('scripts')
        <script>
            document.querySelector('[data-copy-target="codice"]')?.addEventListener('click', function () {
                const value = document.querySelector('.donation-five-per-mille__codice-value')?.dataset.copyValue;
                if (!value) return;
                navigator.clipboard?.writeText(value);
                const copied = this.dataset.copiedLabel;
                const original = this.dataset.copyLabel;
                this.textContent = copied;
                setTimeout(() => { this.textContent = original; }, 2000);
            });
        </script>
    @endpush
@endsection
