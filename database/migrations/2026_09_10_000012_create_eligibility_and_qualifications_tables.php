<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('eligibility_rules', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('notice_id')->constrained('notices')->cascadeOnDelete();
            $table->integer('minimum_age')->nullable();
            $table->integer('maximum_age')->nullable();
            $table->date('age_calculated_as_on')->nullable();
            $table->jsonb('age_relaxation_json')->default('{}');
            $table->text('qualification_summary')->nullable();
            $table->text('experience_text')->nullable();
            $table->string('nationality_text', 255)->default('Citizen of India');
            $table->text('raw_eligibility_text')->nullable();
            $table->timestampsTz();

            $table->index('notice_id');
        });

        Schema::create('qualifications', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->string('level', 50);
            $table->string('discipline', 100)->nullable();
            $table->timestampsTz();
        });

        Schema::create('notice_qualifications', function (Blueprint $table) {
            $table->foreignUlid('notice_id')->constrained('notices')->cascadeOnDelete();
            $table->foreignUlid('qualification_id')->constrained('qualifications')->restrictOnDelete();
            $table->boolean('is_mandatory')->default(true);
            $table->decimal('min_percentage', 4, 2)->nullable();

            $table->primary(['notice_id', 'qualification_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notice_qualifications');
        Schema::dropIfExists('qualifications');
        Schema::dropIfExists('eligibility_rules');
    }
};
