@php
    /** @var \App\Services\DonationSettingsService $donationSettings */
    $bank = $donationSettings->bankTransfer();
    $iban = $donationSettings->iban();
    $beneficiary = trim((string) ($bank['beneficiary'] ?? ''));
@endphp

@if ($donationSettings->bankTransferEnabled() && ($iban !== '' || $beneficiary !== ''))
    <section class="donation-bank-transfer safehouse-glass mb-10 rounded-2xl p-6 md:p-8" aria-labelledby="bank-transfer-heading">
        <h2 id="bank-transfer-heading" class="mb-3 text-xl font-semibold">
            {{ $donationSettings->localized($bank, 'heading') }}
        </h2>

        @if ($donationSettings->localized($bank, 'body') !== '')
            <div class="safehouse-prose mb-6 max-w-none text-safehouse-muted">
                {!! $donationSettings->localized($bank, 'body') !!}
            </div>
        @endif

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($beneficiary !== '')
                <div>
                    <dt class="text-sm text-safehouse-muted">{{ $donationSettings->localized($bank, 'beneficiary_label') }}</dt>
                    <dd class="mt-1 font-medium">{{ $beneficiary }}</dd>
                </div>
            @endif

            @if ($iban !== '')
                <div class="sm:col-span-2">
                    <dt class="text-sm text-safehouse-muted">{{ $donationSettings->localized($bank, 'iban_label') }}</dt>
                    <dd class="mt-1 flex flex-wrap items-center gap-3">
                        <span class="font-mono text-lg tracking-wide" data-copy-value="{{ $iban }}">{{ $iban }}</span>
                        <button type="button" class="safehouse-btn-secondary text-sm" data-copy-iban data-copied-label="{{ __('site.donations.copied') }}">
                            {{ __('site.donations.copy_iban') }}
                        </button>
                    </dd>
                </div>
            @endif
        </dl>
    </section>

    @once
        @push('scripts')
            <script>
                document.querySelector('[data-copy-iban]')?.addEventListener('click', function () {
                    const value = this.closest('dd')?.querySelector('[data-copy-value]')?.dataset.copyValue;
                    if (!value) return;
                    navigator.clipboard?.writeText(value);
                    const original = this.textContent;
                    this.textContent = this.dataset.copiedLabel;
                    setTimeout(() => { this.textContent = original; }, 2000);
                });
            </script>
        @endpush
    @endonce
@endif
