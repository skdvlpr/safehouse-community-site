<?php

namespace Tests\Feature;

use App\Models\Volunteer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VolunteerModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteers_migration_runs(): void
    {
        $this->assertTrue(Schema::hasTable('volunteers'));
    }

    public function test_volunteers_table_stores_hashes_not_raw_ip(): void
    {
        $columns = Schema::getColumnListing('volunteers');

        $this->assertContains('ip_hash', $columns);
        $this->assertContains('user_agent_hash', $columns);
        $this->assertNotContains('ip', $columns);
        $this->assertNotContains('ip_address', $columns);
        $this->assertNotContains('user_agent', $columns);
    }

    public function test_volunteer_model_uses_expected_fillable_fields(): void
    {
        $volunteer = new Volunteer;

        $this->assertSame([
            'name',
            'email',
            'phone',
            'message',
            'status',
            'ip_hash',
            'user_agent_hash',
            'gdpr_consent_at',
        ], $volunteer->getFillable());
    }

    public function test_volunteer_factory_persists_record(): void
    {
        $volunteer = Volunteer::factory()->create([
            'name' => 'Maria Rossi',
            'email' => 'maria@example.com',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('volunteers', [
            'id' => $volunteer->id,
            'name' => 'Maria Rossi',
            'email' => 'maria@example.com',
            'status' => 'pending',
        ]);
    }
}
