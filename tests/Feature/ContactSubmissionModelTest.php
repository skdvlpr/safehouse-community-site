<?php

namespace Tests\Feature;

use App\Models\ContactSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContactSubmissionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_submissions_migration_runs(): void
    {
        $this->assertTrue(Schema::hasTable('contact_submissions'));
    }

    public function test_contact_submissions_table_stores_hashes_not_raw_ip_or_user_agent(): void
    {
        $columns = Schema::getColumnListing('contact_submissions');

        $this->assertContains('ip_hash', $columns);
        $this->assertContains('user_agent_hash', $columns);
        $this->assertNotContains('ip', $columns);
        $this->assertNotContains('ip_address', $columns);
        $this->assertNotContains('user_agent', $columns);
    }

    public function test_contact_submission_factory_persists_record(): void
    {
        $submission = ContactSubmission::factory()->create([
            'name' => 'Luca Bianchi',
            'email' => 'luca@example.com',
            'message' => 'Buongiorno, vorrei informazioni.',
        ]);

        $this->assertDatabaseHas('contact_submissions', [
            'id' => $submission->id,
            'name' => 'Luca Bianchi',
            'email' => 'luca@example.com',
            'status' => 'new',
        ]);
    }
}
