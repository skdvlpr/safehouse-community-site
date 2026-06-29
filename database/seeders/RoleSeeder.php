<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Seed CMS roles for Filament panel access.
     */
    public function run(): void
    {
        $guard = 'web';

        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => $guard]);
        Role::firstOrCreate(['name' => 'editor', 'guard_name' => $guard]);
    }
}
