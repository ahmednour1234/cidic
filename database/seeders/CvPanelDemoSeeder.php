<?php

namespace Database\Seeders;

use App\Enums\Department;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Nationality;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo panel staff, one per department, for local testing only.
 *
 * Never run this in production: the passwords are shared and well known.
 */
class CvPanelDemoSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::firstOrCreate(['name' => 'الفرع الرئيسي'], ['is_active' => true]);

        $staff = [
            ['منسّق التنسيق', 'coordinator@example.com', Department::Coordination],
            ['موظف خدمة العملاء', 'agent@example.com', Department::CustomerService],
            ['مدير الفرع', 'manager@example.com', Department::BranchManager],
        ];

        foreach ($staff as [$name, $email, $department]) {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => UserRole::Staff->value,
                    'department' => $department->value,
                    'branch_id' => $branch->id,
                    'is_active' => true,
                ],
            );

            // The coordinator needs nationalities to see or upload anything.
            if ($department === Department::Coordination) {
                $user->nationalities()->sync(Nationality::pluck('id')->all());
            }
        }

        $this->command?->info('Demo panel users: coordinator@ / agent@ / manager@example.com — password: password');
    }
}
