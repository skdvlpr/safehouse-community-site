<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateCmsUserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_field_is_dehydrated_so_create_receives_it(): void
    {
        $schema = UserResource::form(Schema::make());
        $components = collect($schema->getComponents());

        $role = $components->first(fn ($c) => method_exists($c, 'getName') && $c->getName() === 'role');
        $this->assertNotNull($role);
        $this->assertTrue($role->isDehydrated());
    }

    public function test_creating_journalist_assigns_spatie_role_and_can_access_panel(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Filament::setCurrentPanel(Filament::getPanel('cms-safehouse'));

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'Giornalista Test',
                'email' => 'giornalista.test@example.com',
                'password' => 'SecurePassw0rd!',
                'role' => 'journalist',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $created = User::query()->where('email', 'giornalista.test@example.com')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole('journalist'));
        $this->assertTrue($created->canAccessPanel(Filament::getPanel('cms-safehouse')));
    }
}
