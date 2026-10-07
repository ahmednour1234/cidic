<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Minimal contract table. The panel only needs to know whether a worker is
     * bound to a live contract; the full contract workflow is out of scope and
     * can extend this table later without touching the guards.
     */
    public function up(): void
    {
        Schema::create('recruitment_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('number', 64)->nullable()->unique();
            $table->foreignId('worker_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            // Integer status so Worker::CONTRACT_ENDED can compare numerically.
            $table->unsignedTinyInteger('current_status')->default(1);
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('current_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_contracts');
    }
};
