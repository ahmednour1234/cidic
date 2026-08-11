<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_request_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_request_id')
                ->constrained('candidate_requests')
                ->cascadeOnDelete();
            $table->string('old_status', 32)->nullable();
            $table->string('new_status', 32);
            // Preserve the audit trail even if the acting user is deleted.
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('candidate_request_id', 'crsh_request_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_request_status_histories');
    }
};
