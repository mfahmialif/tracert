<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::create([
            'username' => 'superadmin',
            'password' => Hash::make('password123'),
            'role_id' => Role::SUPERADMIN,
        ]);

        // Admin
        User::create([
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'role_id' => Role::ADMIN,
        ]);
    }
}
