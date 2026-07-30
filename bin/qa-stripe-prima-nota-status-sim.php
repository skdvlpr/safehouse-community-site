<?php

/**
 * QA: simulate Stripe payout / refund / cancel against existing DDEV PrimaNota rows,
 * then print dashlet summary before/after for manual verification.
 *
 * Limitations (Stripe test account):
 *   - Real bank payouts require available balance. This account currently parks
 *     funds in pending (available_on in the future) → payouts are SIMULATED by
 *     applying the same CRM updates as payout.paid (Inviato + payout metadata).
 *   - Refunds call the real Stripe Refunds API, then apply charge.refunded via
 *     PrimaNotaPaymentStatusService (same path as webhooks).
 *   - Cancels apply payment_intent.canceled via the status service (no Stripe
 *     cancel API — PI already succeeded).
 *
 * Usage (site DDEV):
 *   ddev exec php bin/qa-stripe-prima-nota-status-sim.php
 *   ddev exec php bin/qa-stripe-prima-nota-status-sim.php --dry-run
 */

declare(strict_types=1);

use App\Services\Donations\PrimaNotaPaymentStatusService;
use App\Services\EspoCrm\EspoCrmClient;
use App\Services\Payments\StripePaymentService;
use App\Support\IntegrationConfig;
use Illuminate\Contracts\Console\Kernel;
use Stripe\Event;
use Stripe\StripeClient;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (app()->environment('production')) {
    fwrite(STDERR, "REFUSED: production\n");
    exit(1);
}

$dryRun = in_array('--dry-run', $argv, true);

$secret = IntegrationConfig::string('stripe.secret');
if ($secret === '' || ! str_starts_with($secret, 'sk_test_')) {
    fwrite(STDERR, "Need sk_test_ Stripe secret\n");
    exit(1);
}

$stripe = new StripeClient($secret);
$crm = app(EspoCrmClient::class);
$status = app(PrimaNotaPaymentStatusService::class);
$stripeService = new StripePaymentService($stripe);
app()->instance(StripePaymentService::class, $stripeService);
$status = app(PrimaNotaPaymentStatusService::class);

$entity = (string) config('espocrm.prima_nota.entity', 'PrimaNota');

/**
 * @return list<array<string, mixed>>
 */
function fetchPlannedStripeRows(EspoCrmClient $crm, string $entity): array
{
    $result = $crm->search($entity, [
        'select' => 'id,name,paymentStatus,amount,amountIn,donationPaymentReference,stripeChargeId,stripeBalanceTransactionId,stripePayoutId,transactionDate',
        'maxSize' => 50,
        'where' => [
            [
                'type' => 'equals',
                'attribute' => 'donationPaymentProvider',
                'value' => 'Stripe',
            ],
            [
                'type' => 'equals',
                'attribute' => 'paymentStatus',
                'value' => 'Planned',
            ],
        ],
        'orderBy' => 'createdAt',
        'order' => 'desc',
    ]);

    $list = $result['list'] ?? [];

    return is_array($list) ? array_values(array_filter($list, 'is_array')) : [];
}

/**
 * @return array<string, mixed>
 */
function snapshotCrmSummary(): array
{
    // Prefer host-orchestrated snapshots; inside the site container nested
    // `ddev exec` is unreliable. Optional: SAFEHOUSE_CRM_SUMMARY_JSON env.
    $injected = getenv('SAFEHOUSE_CRM_SUMMARY_JSON');
    if (is_string($injected) && $injected !== '') {
        $decoded = json_decode($injected, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }

    $crmRoot = getenv('SAFEHOUSE_CRM_ROOT') ?: '/home/skoksharov/nonprofit-espocrm';
    if (! is_dir($crmRoot)) {
        return ['error' => 'crm_root_missing'];
    }

    $full = 'cd '.escapeshellarg($crmRoot)
        .' && ddev exec php bin/print-prima-nota-summary.php 2>/dev/null';
    $out = @shell_exec($full);
    if (! is_string($out) || trim($out) === '') {
        return ['error' => 'crm_snapshot_failed_use_host_wrapper'];
    }

    $start = strpos($out, '{');
    $end = strrpos($out, '}');
    if ($start === false || $end === false || $end < $start) {
        return ['error' => 'crm_snapshot_no_json', 'raw' => trim($out)];
    }

    $decoded = json_decode(substr($out, $start, $end - $start + 1), true);

    return is_array($decoded) ? $decoded : ['error' => 'crm_snapshot_json', 'raw' => trim($out)];
}

$rows = fetchPlannedStripeRows($crm, $entity);
if (count($rows) < 4) {
    fwrite(STDERR, 'Need at least 4 Planned Stripe PrimaNota rows, got '.count($rows)."\n");
    exit(1);
}

// Pick deterministic slices from newest Planned Stripe rows.
$payoutTargets = array_slice($rows, 0, 3);
$refundTargets = array_slice($rows, 3, 2);
$cancelTargets = array_slice($rows, 5, 1);
if ($cancelTargets === []) {
    // If fewer than 6, take last remaining Planned after payout/refund picks.
    $used = array_merge(
        array_column($payoutTargets, 'id'),
        array_column($refundTargets, 'id'),
    );
    foreach ($rows as $row) {
        if (! in_array($row['id'] ?? '', $used, true)) {
            $cancelTargets = [$row];
            break;
        }
    }
}

echo "=== Stripe QA status simulation ===\n";
echo $dryRun ? "MODE: dry-run\n" : "MODE: apply\n";

$bal = $stripe->balance->retrieve();
$availableEur = 0;
foreach ($bal->available as $bucket) {
    if (($bucket->currency ?? '') === 'eur') {
        $availableEur = (int) ($bucket->amount ?? 0);
    }
}
echo "Stripe available EUR cents: {$availableEur}\n";
echo 'Payout targets: '.implode(', ', array_column($payoutTargets, 'id'))."\n";
echo 'Refund targets: '.implode(', ', array_column($refundTargets, 'id'))."\n";
echo 'Cancel targets: '.implode(', ', array_column($cancelTargets, 'id'))."\n";

$before = snapshotCrmSummary();
echo "\n--- BEFORE dashlet summary ---\n";
echo json_encode($before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";

$expectedPayoutNet = 0.0;
foreach ($payoutTargets as $row) {
    $expectedPayoutNet += (float) ($row['amount'] ?? $row['amountIn'] ?? 0);
}
$expectedRefundPlannedDrop = 0.0;
foreach ($refundTargets as $row) {
    $expectedRefundPlannedDrop += (float) ($row['amount'] ?? $row['amountIn'] ?? 0);
}
$expectedCancelPlannedDrop = 0.0;
foreach ($cancelTargets as $row) {
    $expectedCancelPlannedDrop += (float) ($row['amount'] ?? $row['amountIn'] ?? 0);
}

echo "\nExpected deltas (approx):\n";
echo "  cashBalance / month.amountIn  += {$expectedPayoutNet} (payout→Inviato)\n";
echo '  month.plannedAmountIn         -= '.($expectedPayoutNet + $expectedRefundPlannedDrop + $expectedCancelPlannedDrop)."\n";
echo "  Refunded/Cancelled leave cash unchanged\n";

echo "\nEXPECT_JSON=".json_encode([
    'payoutNet' => round($expectedPayoutNet, 2),
    'plannedDrop' => round($expectedPayoutNet + $expectedRefundPlannedDrop + $expectedCancelPlannedDrop, 2),
    'payoutIds' => array_column($payoutTargets, 'id'),
    'refundIds' => array_column($refundTargets, 'id'),
    'cancelIds' => array_column($cancelTargets, 'id'),
], JSON_UNESCAPED_UNICODE)."\n";

if ($dryRun) {
    echo "\nDry-run only — no Stripe/CRM mutations.\n";
    exit(0);
}

$actions = [];

// --- Real payout attempt, else simulate CRM updates identical to webhook ---
$realPayoutId = null;
if ($availableEur >= 100) {
    try {
        $payout = $stripe->payouts->create([
            'amount' => min(100, $availableEur),
            'currency' => 'eur',
            'metadata' => ['safehouse_qa_sim' => '1'],
        ]);
        $realPayoutId = (string) $payout->id;
        $event = Event::constructFrom([
            'id' => 'evt_qa_payout_'.uniqid(),
            'type' => 'payout.paid',
            'data' => ['object' => $payout->toArray()],
        ]);
        $result = $status->applyFromStripeEvent($event);
        $actions[] = [
            'type' => 'payout.paid_real',
            'payoutId' => $realPayoutId,
            'result' => $result,
        ];
    } catch (Throwable $e) {
        $actions[] = ['type' => 'payout.paid_real_failed', 'error' => $e->getMessage()];
    }
}

$simPayoutId = $realPayoutId ?: ('po_qa_sim_'.date('YmdHis'));
$simPaidAt = gmdate('Y-m-d H:i:s');

foreach ($payoutTargets as $row) {
    $id = (string) ($row['id'] ?? '');
    if ($id === '') {
        continue;
    }
    // If real payout already flipped this row, skip.
    $current = $crm->search($entity, [
        'select' => 'id,paymentStatus,stripePayoutId',
        'maxSize' => 1,
        'where' => [[
            'type' => 'equals',
            'attribute' => 'id',
            'value' => $id,
        ]],
    ]);
    $curStatus = (string) (($current['list'][0]['paymentStatus'] ?? '') ?: '');
    if ($curStatus === 'Inviato' && ! empty($current['list'][0]['stripePayoutId'] ?? null)) {
        $actions[] = ['type' => 'payout_skip_already_inviato', 'id' => $id];

        continue;
    }

    $crm->update($entity, $id, [
        'paymentStatus' => 'Inviato',
        'stripePayoutId' => $simPayoutId,
        'stripePayoutPaidAt' => $simPaidAt,
    ]);
    $actions[] = [
        'type' => $realPayoutId ? 'payout_crm_confirm' : 'payout_simulated',
        'id' => $id,
        'payoutId' => $simPayoutId,
        'amount' => $row['amount'] ?? null,
        'bt' => $row['stripeBalanceTransactionId'] ?? null,
    ];
}

// --- Real refunds ---
foreach ($refundTargets as $row) {
    $id = (string) ($row['id'] ?? '');
    $chargeId = trim((string) ($row['stripeChargeId'] ?? ''));
    if ($id === '' || $chargeId === '') {
        $actions[] = ['type' => 'refund_skip', 'id' => $id, 'reason' => 'missing_charge'];

        continue;
    }

    try {
        $refund = $stripe->refunds->create([
            'charge' => $chargeId,
            'metadata' => ['safehouse_qa_sim' => '1', 'prima_nota_id' => $id],
        ]);
        $charge = $stripe->charges->retrieve($chargeId);
        $event = Event::constructFrom([
            'id' => 'evt_qa_refund_'.uniqid(),
            'type' => 'charge.refunded',
            'data' => ['object' => $charge->toArray()],
        ]);
        $result = $status->applyFromStripeEvent($event);
        $actions[] = [
            'type' => 'refund_real',
            'id' => $id,
            'chargeId' => $chargeId,
            'refundId' => $refund->id,
            'result' => $result,
        ];
    } catch (Throwable $e) {
        $actions[] = [
            'type' => 'refund_failed',
            'id' => $id,
            'chargeId' => $chargeId,
            'error' => $e->getMessage(),
        ];
    }
}

// --- Cancel (status event only — PI already succeeded) ---
foreach ($cancelTargets as $row) {
    $id = (string) ($row['id'] ?? '');
    $ref = (string) ($row['donationPaymentReference'] ?? '');
    $piId = ltrim($ref, '#');
    if ($id === '' || $piId === '' || ! str_starts_with($piId, 'pi_')) {
        $actions[] = ['type' => 'cancel_skip', 'id' => $id, 'reason' => 'missing_pi'];

        continue;
    }

    $event = Event::constructFrom([
        'id' => 'evt_qa_cancel_'.uniqid(),
        'type' => 'payment_intent.canceled',
        'data' => ['object' => [
            'id' => $piId,
            'object' => 'payment_intent',
            'status' => 'canceled',
        ]],
    ]);
    $result = $status->applyFromStripeEvent($event);
    $actions[] = [
        'type' => 'cancel_event',
        'id' => $id,
        'paymentIntentId' => $piId,
        'result' => $result,
    ];
}

$after = snapshotCrmSummary();

echo "\n--- ACTIONS ---\n";
echo json_encode($actions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";

echo "\n--- AFTER dashlet summary ---\n";
echo json_encode($after, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";

if (isset($before['error']) || isset($after['error'])) {
    echo "\nNOTE: in-container CRM snapshot unavailable — use host wrapper bin/qa-run-stripe-status-sim.sh for assertions.\n";
    exit(0);
}

$beforeCash = (float) ($before['cashBalance'] ?? 0);
$afterCash = (float) ($after['cashBalance'] ?? 0);
$beforeMonthIn = (float) ($before['month']['amountIn'] ?? 0);
$afterMonthIn = (float) ($after['month']['amountIn'] ?? 0);
$beforePin = (float) ($before['month']['plannedAmountIn'] ?? 0);
$afterPin = (float) ($after['month']['plannedAmountIn'] ?? 0);

$cashDelta = round($afterCash - $beforeCash, 2);
$monthInDelta = round($afterMonthIn - $beforeMonthIn, 2);
$pinDelta = round($afterPin - $beforePin, 2);

echo "\n--- DELTAS ---\n";
echo "cashBalance: {$beforeCash} → {$afterCash} (Δ {$cashDelta}); expected ~+{$expectedPayoutNet}\n";
echo "month.amountIn: {$beforeMonthIn} → {$afterMonthIn} (Δ {$monthInDelta}); expected ~+{$expectedPayoutNet}\n";
echo "month.plannedAmountIn: {$beforePin} → {$afterPin} (Δ {$pinDelta}); expected ~-"
    .($expectedPayoutNet + $expectedRefundPlannedDrop + $expectedCancelPlannedDrop)."\n";

$cashOk = abs($cashDelta - $expectedPayoutNet) < 0.05;
$monthOk = abs($monthInDelta - $expectedPayoutNet) < 0.05;
$pinOk = abs($pinDelta + ($expectedPayoutNet + $expectedRefundPlannedDrop + $expectedCancelPlannedDrop)) < 0.05;

echo "\n--- ASSERTIONS ---\n";
echo ($cashOk ? 'PASS' : 'FAIL')." cashBalance rose by payout net\n";
echo ($monthOk ? 'PASS' : 'FAIL')." month.amountIn rose by payout net\n";
echo ($pinOk ? 'PASS' : 'FAIL')." month.plannedAmountIn dropped by payout+refund+cancel nets\n";

exit(($cashOk && $monthOk && $pinOk) ? 0 : 1);
