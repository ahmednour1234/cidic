<?php

namespace Tests\Feature\CvPanel;

use App\Enums\Department;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Nationality;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Shared fixtures for the CV panel suite. */
abstract class CvPanelTestCase extends TestCase
{
    protected function branch(string $name = 'الفرع الرئيسي'): Branch
    {
        return Branch::firstOrCreate(['name' => $name]);
    }

    protected function nationality(string $slug = 'ethiopia', string $code = 'et'): Nationality
    {
        return Nationality::firstOrCreate(
            ['slug' => $slug],
            ['name_ar' => 'جنسية '.$slug, 'code' => $code],
        );
    }

    protected function panelUser(
        ?Department $department,
        UserRole $role = UserRole::Staff,
        array $nationalities = [],
        bool $active = true,
    ): User {
        static $seq = 0;
        $seq++;

        $user = User::create([
            'name' => 'مستخدم '.$seq,
            'email' => 'panel'.$seq.'@example.test',
            'password' => Hash::make('password123'),
            'role' => $role->value,
            'department' => $department?->value,
            'branch_id' => $this->branch()->id,
            'is_active' => $active,
        ]);

        if ($nationalities !== []) {
            $user->nationalities()->sync(collect($nationalities)->pluck('id')->all());
        }

        return $user;
    }

    protected function coordinator(array $nationalities = []): User
    {
        return $this->panelUser(Department::Coordination, nationalities: $nationalities);
    }

    protected function agent(): User
    {
        return $this->panelUser(Department::CustomerService);
    }

    protected function manager(): User
    {
        return $this->panelUser(Department::BranchManager);
    }

    protected function superAdmin(): User
    {
        return $this->panelUser(null, UserRole::SuperAdmin);
    }

    protected function client(string $name = 'عميل تجريبي'): Client
    {
        return Client::create([
            'name' => $name,
            'phone' => '05'.random_int(10000000, 99999999),
            'branch_id' => $this->branch()->id,
        ]);
    }

    protected function worker(array $attributes = []): Worker
    {
        return Worker::create(array_merge([
            'name' => 'عاملة تجريبية',
            'nationality_id' => $this->nationality()->id,
            'experience' => '1-3',
            'religion' => 'muslim',
            'cv_path' => 'cvs/'.uniqid().'.pdf',
            'cv_disk' => 'cv_private',
            'status' => Worker::STATUS_AVAILABLE,
        ], $attributes));
    }
}
