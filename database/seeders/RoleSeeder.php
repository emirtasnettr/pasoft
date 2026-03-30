<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['slug' => 'system_admin', 'name' => 'Sistem Yöneticisi'],
            ['slug' => 'admin', 'name' => 'Yönetici'],
            ['slug' => 'sales', 'name' => 'Satış'],
            ['slug' => 'operations', 'name' => 'Operasyon'],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['slug' => $role['slug']],
                ['name' => $role['name']],
            );
        }
    }
}
