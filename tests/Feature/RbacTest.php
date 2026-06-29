<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_seeder_creates_expected_roles(): void
    {
        $this->seed(RoleSeeder::class);

        $this->assertDatabaseHas('roles', ['name' => 'super-admin']);
        $this->assertDatabaseHas('roles', ['name' => 'editor']);
    }

    public function test_super_admin_can_access_panel(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get('/cms-safehouse');

        $response->assertStatus(200);
    }

    public function test_editor_can_access_panel(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('editor');

        $response = $this->actingAs($user)->get('/cms-safehouse');

        $response->assertStatus(200);
    }

    public function test_user_without_roles_cannot_access_panel(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/cms-safehouse');

        $response->assertStatus(403);
    }
}
