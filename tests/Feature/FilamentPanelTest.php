<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_filament_panel_boots_on_custom_path(): void
    {
        $response = $this->get('/cms-safehouse/login');
        $response->assertStatus(200);
    }

    public function test_admin_path_returns_404(): void
    {
        $response = $this->get('/admin');
        $response->assertStatus(404);
    }

    public function test_auth_redirects_for_guests(): void
    {
        $response = $this->get('/cms-safehouse');
        $response->assertRedirect('/cms-safehouse/login');
    }
}
