<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Encrypted at rest; the *_hash columns carry the searchable HMAC.
            $table->text('phone')->nullable();
            $table->string('phone_hash', 64)->nullable();
            $table->text('national_id')->nullable();
            $table->string('national_id_hash', 64)->nullable();
            $table->string('classification', 32)->default('confirmed');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('phone_hash');
            $table->index('national_id_hash');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
