<?php

namespace Tests\Feature;

use App\Models\GdprConsent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GdprConsentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_gdpr_consents_migration_runs(): void
    {
        $this->assertTrue(Schema::hasTable('gdpr_consents'));
    }

    public function test_gdpr_consents_table_stores_hash_not_raw_ip(): void
    {
        $columns = Schema::getColumnListing('gdpr_consents');

        $this->assertContains('ip_hash', $columns);
        $this->assertNotContains('ip', $columns);
        $this->assertNotContains('ip_address', $columns);
    }

    public function test_gdpr_consent_factory_persists_record(): void
    {
        $consent = GdprConsent::factory()->create([
            'consent_type' => 'cookie_banner',
            'granted' => true,
        ]);

        $this->assertDatabaseHas('gdpr_consents', [
            'id' => $consent->id,
            'consent_type' => 'cookie_banner',
            'granted' => true,
        ]);
    }
}
