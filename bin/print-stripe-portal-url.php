<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$secret = App\Support\IntegrationConfig::string('stripe.secret');
if ($secret === '') {
    fwrite(STDERR, "empty secret\n");
    exit(1);
}

$client = new Stripe\StripeClient($secret);
$configs = $client->billingPortal->configurations->all(['limit' => 10]);

if (count($configs->data) === 0) {
    $created = $client->billingPortal->configurations->create([
        'features' => [
            'customer_update' => ['enabled' => true, 'allowed_updates' => ['email']],
            'invoice_history' => ['enabled' => true],
            'payment_method_update' => ['enabled' => true],
            'subscription_cancel' => ['enabled' => true],
        ],
        'business_profile' => [
            'headline' => 'Safe House — gestisci la tua donazione',
        ],
    ]);
    echo "created_config={$created->id}\n";
    $configs = $client->billingPortal->configurations->all(['limit' => 10]);
}

foreach ($configs->data as $config) {
    $updated = $client->billingPortal->configurations->update($config->id, [
        'login_page' => ['enabled' => true],
        'features' => [
            'subscription_cancel' => [
                'enabled' => true,
                'mode' => 'at_period_end',
            ],
            'payment_method_update' => ['enabled' => true],
            'invoice_history' => ['enabled' => true],
        ],
    ]);
    $url = (string) ($updated->login_page->url ?? '');
    echo "config={$updated->id}\n";
    echo "url={$url}\n";
    echo "active=".(($updated->active ?? false) ? '1' : '0')."\n";
}
