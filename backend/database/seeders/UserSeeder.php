<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'username' => 'superadmin',
                'password' => 'superadmin123',
                'role_id' => Role::SUPERADMIN,
                'level' => 'Superadmin',
            ],
            [
                'username' => 'admin',
                'password' => 'admin123',
                'role_id' => Role::ADMIN,
                'level' => 'Admin',
            ],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['username' => $account['username']],
                [
                    'password' => Hash::make($account['password']),
                    'role_id' => $account['role_id'],
                ],
            );
        }

        $this->command?->newLine();
        $this->command?->info('Akun login hasil seeding:');
        $this->command?->table(
            ['Level', 'Username', 'Password'],
            array_map(
                fn (array $account) => [
                    $account['level'],
                    $account['username'],
                    $account['password'],
                ],
                $accounts,
            ),
        );
    }
}
