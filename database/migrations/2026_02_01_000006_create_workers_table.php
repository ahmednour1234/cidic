<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            // Taken from the uploaded PDF's file name, so it may be absent.
            $table->string('name')->nullable();

            // Encrypted at rest; the *_hash columns carry the searchable HMAC.
            $table->text('passport_number')->nullable();
            $table->string('passport_number_hash', 64)->nullable();
            $table->text('phone')->nullable();
            $table->string('phone_hash', 64)->nullable();

            $table->foreignId('nationality_id')->nullable()->constrained()->nullOnDelete();
            $table->string('profession')->nullable();
            // Option keys live in lang/*/workers.php.
            $table->string('experience', 16)->nullable();
            $table->string('religion', 16)->nullable();
            $table->string('gender', 16)->default('female');
            $table->unsignedSmallInteger('age')->nullable();

            $table->string('cv_path')->nullable();
            // Null means the legacy `public` disk; new uploads set cv_private.
            $table->string('cv_disk', 20)->nullable();
            $table->string('original_cv_name')->nullable();

            // Stamped once, the first time the worker is booked. Never cleared
            // automatically - withdrawal from the public site is permanent.
            $table->timestamp('cv_withdrawn_at')->nullable();

            $table->string('status', 32)->default('available');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('assigned_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();

            $table->timestamp('tamara_paid_at')->nullable();
            $table->foreignId('tamara_paid_by_admin_id')->nullable()->constrained('users')->nullOnDelete();

            // The uploader.
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();

            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('cv_withdrawn_at');
            $table->index('passport_number_hash');
            $table->index('phone_hash');
            $table->index(['active', 'status']);
            $table->index('original_cv_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};
