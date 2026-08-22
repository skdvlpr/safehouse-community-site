<?php

/**
 * DDEV QA: create N Stripe test PaymentIntents, settle, ingest into CRM, attach CRM link.
 *
 * Run AFTER CRM purge. Requires site DDEV + sk_test_ + ESPOCRM pointing at local CRM.
 *
 * Usage (site):
 *   ddev exec php bin/qa-seed-stripe-prima-nota.php --count=4
 *   ddev exec php bin/qa-seed-stripe-prima-nota.php --count=2 --bypass-pending
 *
 * --bypass-pending uses pm_card_bypassPending so funds land in available balance
 * immediately (for manual payout QA). Default pm_card_visa stays in pending until available_on.
 */
declare(strict_types=1);

use App\Models\DonationCampaign;
use App\Services\Donations\DonationIngestPayloadMapper;
use App\Services\Donations\DonationIngestService;
use App\Services\Payments\StripePaymentService;
use App\Support\IntegrationConfig;
use Illuminate\Contracts\Console\Kernel;
use Stripe\StripeClient;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (app()->environment('production')) {
    fwrite(STDERR, "REFUSED: production\n");
    exit(1);
}

$count = 4;
$bypassPending = false;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--count=')) {
        $count = max(1, min(5, (int) substr($arg, strlen('--count='))));
    }
    if ($arg === '--bypass-pending') {
        $bypassPending = true;
    }
}

$secret = IntegrationConfig::string('stripe.secret');
if ($secret === '' || ! str_starts_with($secret, 'sk_test_')) {
    $local = database_path('seeders/data/local-integrations.php');
    if (is_readable($local)) {
        /** @var mixed $data */
        $data = require $local;
        if (is_array($data) && isset($data['stripe.secret'])) {
            $secret = (string) $data['stripe.secret'];
        }
    }
}
if ($secret === '' || ! str_starts_with($secret, 'sk_test_')) {
    fwrite(STDERR, "Need sk_test_ secret\n");
    exit(1);
}

config()->set('stripe.secret', $secret);
config()->set('stripe.mock', false);

$paymentMethod = $bypassPending ? 'pm_card_bypassPending' : 'pm_card_visa';
$crmBase = rtrim((string) config('espocrm.base_url'), '/');
echo "CRM base: {$crmBase}\n";
echo "Creating {$count} Stripe sandbox donations…\n";
echo "payment_method: {$paymentMethod}".($bypassPending ? " (instant available)\n" : "\n");

$campaign = DonationCampaign::query()
    ->where('allows_recurring', false)
    ->orderByDesc('id')
    ->first();

if ($campaign === null) {
    $campaign = DonationCampaign::factory()->create([
        'espocrm_finanziamento_name' => 'QA Seed Raccolta '.date('Ymd-His'),
        'currency' => 'EUR',
        'allows_recurring' => false,
        'allow_custom_amount' => true,
        'min_amount' => 1,
    ]);
}

$stripe = new StripeClient($secret);
$stripeService = new StripePaymentService($stripe);
app()->instance(StripePaymentService::class, $stripeService);

$ingest = app(DonationIngestService::class);
$mapper = app(DonationIngestPayloadMapper::class);

$created = [];

for ($i = 1; $i <= $count; $i++) {
    $amountCents = 500 + ($i * 50); // €5.50, €6.00, …
    $donor = $bypassPending ? "QA Instant Donor {$i}" : "QA Seed Donor {$i}";
    $emailPrefix = $bypassPending ? 'qa-instant' : 'qa-seed';

    $intent = $stripe->paymentIntents->create([
        'amount' => $amountCents,
        'currency' => 'eur',
        'confirm' => true,
        'payment_method' => $paymentMethod,
        'automatic_payment_methods' => [
            'enabled' => true,
            'allow_redirects' => 'never',
        ],
        'description' => $stripeService->donationDescription($campaign, $donor, 'OneTime'),
        'metadata' => [
            'campaign_id' => (string) $campaign->id,
            'campaign_title' => mb_substr($campaign->finanziamentoTitle(), 0, 500),
            'donor_name' => $donor,
            'donor_type' => 'individual',
            'donor_email' => "{$emailPrefix}-{$i}@example.com",
            'donation_frequency' => 'OneTime',
            'safehouse_qa_seed' => '1',
            'safehouse_qa_bypass_pending' => $bypassPending ? '1' : '0',
        ],
        'receipt_email' => "{$emailPrefix}-{$i}@example.com",
    ]);

    if ($intent->status !== 'succeeded') {
        fwrite(STDERR, "PI not succeeded: {$intent->id} status={$intent->status}\n");
        exit(1);
    }

    $settled = $stripeService->retrieveSettledPaymentIntent($intent->id);
    $payload = $mapper->fromPaymentIntent($settled);
    $result = $ingest->ingest($payload);

    // Re-fetch PI for description + CRM metadata asserts
    $updated = $stripe->paymentIntents->retrieve($intent->id);
    $meta = $updated->metadata?->toArray() ?? [];

    $row = [
        'i' => $i,
        'pi' => $intent->id,
        'prima_nota_id' => $result['prima_nota_id'] ?? '',
        'ingest_status' => $result['status'] ?? '',
        'description' => (string) ($updated->description ?? ''),
        'crm_url' => (string) ($meta['crm_prima_nota_url'] ?? ''),
        'crm_id_meta' => (string) ($meta['crm_prima_nota_id'] ?? ''),
        'amount_cents' => $amountCents,
    ];
    $created[] = $row;
    echo json_encode($row, JSON_UNESCAPED_UNICODE)."\n";

    if (($row['prima_nota_id'] ?? '') === '') {
        fwrite(STDERR, "FAIL: missing prima_nota_id\n");
        exit(1);
    }
    if (trim($row['description']) === '') {
        fwrite(STDERR, "FAIL: empty Stripe description\n");
        exit(1);
    }
    if ($row['crm_url'] === '' || ! str_contains($row['crm_url'], $row['prima_nota_id'])) {
        fwrite(STDERR, "FAIL: CRM URL metadata missing\n");
        exit(1);
    }
}

echo 'CREATED='.count($created)."\n";
echo 'IDS='.implode(',', array_column($created, 'prima_nota_id'))."\n";
exit(0);
