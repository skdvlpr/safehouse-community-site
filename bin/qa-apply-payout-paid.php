<?php
/**
 * QA: apply payout.paid for an existing Stripe payout against local CRM.
 *
 * Automatic payouts only — manual payouts are ignored by design.
 *
 * Usage:
 *   ddev exec php bin/qa-apply-payout-paid.php po_xxx
 */
declare(strict_types=1);

use App\Services\Donations\PrimaNotaPaymentStatusService;
use App\Services\Payments\StripePaymentService;
use App\Support\IntegrationConfig;
use Illuminate\Contracts\Console\Kernel;
use Stripe\Event;
use Stripe\StripeClient;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (app()->environment('production')) {
    fwrite(STDERR, "REFUSED: production\n");
    exit(1);
}

$payoutId = $argv[1] ?? '';
if ($payoutId === '' || ! str_starts_with($payoutId, 'po_')) {
    fwrite(STDERR, "Usage: php bin/qa-apply-payout-paid.php po_...\n");
    exit(1);
}

$secret = IntegrationConfig::string('stripe.secret');
if ($secret === '' || ! str_starts_with($secret, 'sk_test_')) {
    $data = require database_path('seeders/data/local-integrations.php');
    $secret = (string) ($data['stripe.secret'] ?? '');
}
if ($secret === '' || ! str_starts_with($secret, 'sk_test_')) {
    fwrite(STDERR, "Need sk_test_ secret\n");
    exit(1);
}

$stripe = new StripeClient($secret);
$stripeService = new StripePaymentService($stripe);
app()->instance(StripePaymentService::class, $stripeService);
$status = app(PrimaNotaPaymentStatusService::class);

$payout = $stripe->payouts->retrieve($payoutId);
echo json_encode([
    'id' => $payout->id,
    'status' => $payout->status,
    'amount' => $payout->amount,
    'automatic' => $payout->automatic,
], JSON_UNESCAPED_SLASHES) . "\n";

$event = Event::constructFrom([
    'id' => 'evt_qa_apply_payout_' . uniqid(),
    'type' => 'payout.paid',
    'data' => ['object' => $payout->toArray()],
]);

$result = $status->applyFromStripeEvent($event);
echo 'RESULT=' . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n";
