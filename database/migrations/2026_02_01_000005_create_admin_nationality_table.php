<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_nationality', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('nationality_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['admin_id', 'nationality_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_nationality');
    }
};
