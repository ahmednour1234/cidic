<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->nullable()->constrained()->nullOnDelete();
            // Denormalised so the log survives the worker row being purged.
            $table->string('worker_name')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('admin_name')->nullable();
            $table->string('action', 32);
            $table->text('label')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('action');
            $table->index(['worker_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_activity_logs');
    }
};
