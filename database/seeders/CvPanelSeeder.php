<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Nationality;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Baseline data for the CV panel.
 *
 * There is only one branch today, but the column exists so additional
 * branches can be added later without a migration.
 */
class CvPanelSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::firstOrCreate(
            ['name' => 'الفرع الرئيسي'],
            ['is_active' => true],
        );

        // Existing staff and clients belong to the only branch there is.
        User::whereNull('branch_id')->update(['branch_id' => $branch->id]);
        Client::whereNull('branch_id')->update(['branch_id' => $branch->id]);

        // The panel routes nationalities by ISO code; backfill any that are
        // missing one so their public URLs resolve.
        $codes = [
            'ethiopia' => 'et',
            'kenya' => 'ke',
            'uganda' => 'ug',
            'philippines' => 'ph',
            'bangladesh' => 'bd',
            'sri-lanka' => 'lk',
            'india' => 'in',
            'nepal' => 'np',
            'indonesia' => 'id',
            'ghana' => 'gh',
            'rwanda' => 'rw',
            'burundi' => 'bi',
        ];

        foreach ($codes as $slug => $code) {
            Nationality::where('slug', $slug)->whereNull('code')->update(['code' => $code]);
        }
    }
}
