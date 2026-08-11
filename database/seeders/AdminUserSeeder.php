<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * DEVELOPMENT CREDENTIALS ONLY.
         *
         * These exist so a fresh install is usable immediately.
         * CHANGE THE PASSWORD BEFORE DEPLOYING TO PRODUCTION.
         */
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('password'),
                'role' => UserRole::SuperAdmin,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        User::updateOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'موظف الطلبات',
                'password' => Hash::make('password'),
                'role' => UserRole::Staff,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $this->command?->warn('Seeded development credentials (admin@example.com / password) — change them before production.');
    }
}
