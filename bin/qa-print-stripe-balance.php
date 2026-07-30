<?php
declare(strict_types=1);

use App\Support\IntegrationConfig;
use Illuminate\Contracts\Console\Kernel;
use Stripe\StripeClient;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (app()->environment('production')) {
    fwrite(STDERR, "REFUSED: production\n");
    exit(1);
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

$balance = (new StripeClient($secret))->balance->retrieve();
echo "available:\n";
foreach ($balance->available as $row) {
    echo json_encode($row) . "\n";
}
echo "pending:\n";
foreach ($balance->pending as $row) {
    echo json_encode($row) . "\n";
}
