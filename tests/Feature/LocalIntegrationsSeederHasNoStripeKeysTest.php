<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocalIntegrationsSeederHasNoStripeKeysTest extends TestCase
{
    public function test_local_integrations_file_contains_no_stripe_key_material(): void
    {
        $path = database_path('seeders/data/local-integrations.php');

        $this->assertFileExists($path);

        $contents = (string) file_get_contents($path);

        $this->assertStringNotContainsString('sk_test_', $contents);
        $this->assertStringNotContainsString('pk_test_', $contents);
        $this->assertStringNotContainsString('sk_live_', $contents);
        $this->assertStringNotContainsString('pk_live_', $contents);
    }
}
