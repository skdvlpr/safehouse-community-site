<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@safehouse.community'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Safehouse',
                'password' => Hash::make('password'),
            ],
        );

        if (! Hash::check('password', $admin->password)) {
            $admin->password = Hash::make('password');
            $admin->save();
        }

        $admin->syncRoles(['super-admin']);
    }
}
