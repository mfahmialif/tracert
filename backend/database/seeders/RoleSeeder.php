<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            Role::SUPERADMIN => 'superadmin',
            Role::ADMIN => 'admin',
            Role::ALUMNI => 'alumni',
        ];

        foreach ($roles as $id => $name) {
            Role::updateOrCreate(
                ['role_id' => $id],
                ['role_name' => $name],
            );
        }
    }
}
