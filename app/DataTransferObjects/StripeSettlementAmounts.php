<?php

namespace App\DataTransferObjects;

/**
 * Settlement amounts taken from Stripe (PaymentIntent + BalanceTransaction).
 * All values are major currency units (e.g. EUR), not cents.
 */
readonly class StripeSettlementAmounts
{
    public function __construct(
        public float $gross,
        public float $fee,
        public float $net,
        public float $feePercent,
        public string $currency,
    ) {}

    /**
     * @param  array{gross_cents: int, fee_cents: int, net_cents: int, currency: string}  $cents
     */
    public static function fromCents(array $cents): self
    {
        $grossCents = max(0, (int) $cents['gross_cents']);
        $feeCents = max(0, (int) $cents['fee_cents']);
        $netCents = (int) $cents['net_cents'];
        $currency = strtoupper((string) $cents['currency']);

        if ($netCents < 0) {
            $netCents = max(0, $grossCents - $feeCents);
        }

        $gross = $grossCents / 100;
        $fee = $feeCents / 100;
        $net = $netCents / 100;
        $feePercent = $grossCents > 0
            ? round($feeCents * 100 / $grossCents, 4)
            : 0.0;

        return new self($gross, $fee, $net, $feePercent, $currency);
    }
}
