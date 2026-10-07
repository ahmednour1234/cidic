<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // The CV panel's own role axis. Null means the user is not panel staff;
            // the existing `role` column keeps governing the main admin area.
            $table->string('department', 32)->nullable()->after('role');
            $table->foreignId('branch_id')->nullable()->after('department')
                ->constrained('branches')->nullOnDelete();

            $table->index('department');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['department']);
            $table->dropColumn(['department', 'branch_id']);
        });
    }
};
