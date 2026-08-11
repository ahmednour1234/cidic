<?php

use App\Enums\RequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 32)->unique();

            $table->string('name');
            $table->string('mobile', 32);
            $table->string('whatsapp', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('city', 128)->nullable();

            // Lookups are optional: the customer may not know exactly what they need.
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('nationality_id')->nullable()->constrained('nationalities')->nullOnDelete();
            $table->foreignId('candidate_category_id')->nullable()->constrained('candidate_categories')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

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
            $table->index(['mobile', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_requests');
    }
};
