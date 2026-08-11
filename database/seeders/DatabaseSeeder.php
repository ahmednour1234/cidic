<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Order matters: candidates depend on nationalities and categories.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SiteSettingSeeder::class,
            ReferenceDataSeeder::class,
            PageSeeder::class,
            DemoCandidateSeeder::class,
        ]);
    }
}
