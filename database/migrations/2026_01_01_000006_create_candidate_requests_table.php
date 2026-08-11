<?php

use App\Enums\RequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 32)->unique();

            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            // Staff member handling the lead; kept when that user is removed.
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->string('customer_name');
            $table->string('mobile', 32);
            $table->string('whatsapp', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('city', 128)->nullable();
            $table->string('service_type', 128)->nullable();
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();

            $table->string('status', 32)->default(RequestStatus::New->value);
            $table->string('source', 64)->default('website');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('created_at');
            $table->index('mobile');
            // Supports the duplicate-submission guard (same candidate + mobile, recent).
            $table->index(['candidate_id', 'mobile', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_requests');
    }
};
