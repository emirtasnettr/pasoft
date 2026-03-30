<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemAdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::query()->where('slug', 'system_admin')->firstOrFail();

        User::query()->updateOrCreate(
            ['email' => 'sistem@policeasist.test'],
            [
                'name' => 'Sistem Yöneticisi',
                'password' => Hash::make('password'),
                'role_id' => $role->id,
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        );
    }
}
