<?php

use App\Enums\AvailabilityStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 32)->unique();
            $table->string('name');
            $table->string('slug')->unique();

            $table->foreignId('nationality_id')->constrained('nationalities')->restrictOnDelete();
            $table->foreignId('candidate_category_id')->constrained('candidate_categories')->restrictOnDelete();

            $table->date('date_of_birth')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('gender', 16)->default('female');
            $table->string('religion', 64)->nullable();
            $table->string('marital_status', 32)->nullable();
            $table->unsignedTinyInteger('children_count')->nullable();
            $table->string('education', 128)->nullable();
            $table->string('profession');
            $table->unsignedTinyInteger('years_of_experience')->default(0);

            $table->decimal('salary', 10, 2)->nullable();
            $table->decimal('contract_price', 10, 2)->nullable();

            // Proficiency levels: none | basic | good | fluent
            $table->string('language_arabic', 16)->default('none');
            $table->string('language_english', 16)->default('none');
            $table->string('other_languages')->nullable();

            $table->json('skills')->nullable();
            $table->json('previous_countries')->nullable();
            $table->text('description')->nullable();

            $table->string('profile_image')->nullable();
            $table->string('cv_file')->nullable();
            $table->string('intro_video')->nullable();

            $table->string('availability_status', 32)->default(AvailabilityStatus::Available->value);
            $table->boolean('featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('availability_status');
            $table->index('is_active');
            $table->index('featured');
            $table->index('created_at');
            $table->index('years_of_experience');
            $table->index('age');
            // Drives the default public listing query (active + available, ordered).
            $table->index(['is_active', 'availability_status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
