<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nationalities', function (Blueprint $table) {
            // The panel addresses nationalities by ISO alpha-2 (/nationality/et).
            // `country_code` already exists but is nullable, free-form and not
            // unique, so it cannot serve as a route key on its own.
            $table->string('code', 2)->nullable()->after('slug');
        });

        // Seed `code` from any existing two-letter country_code.
        DB::table('nationalities')
            ->whereNull('code')
            ->whereRaw('LENGTH(country_code) = 2')
            ->update(['code' => DB::raw('LOWER(country_code)')]);
    }

    public function down(): void
    {
        Schema::table('nationalities', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
