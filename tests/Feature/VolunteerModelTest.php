<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VolunteerModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteers_table_is_absent(): void
    {
        $this->assertFalse(Schema::hasTable('volunteers'));
    }
}
