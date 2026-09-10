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
        Schema::create('positions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('notice_id')->constrained('notices')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('post_code', 100)->nullable();
            $table->string('department', 255)->nullable();
            $table->integer('total_vacancies')->default(0);
            $table->string('employment_type', 50)->default('permanent');
            $table->string('pay_level', 50)->nullable();
            $table->string('pay_scale_text', 255)->nullable();
            $table->integer('salary_min')->nullable();
            $table->integer('salary_max')->nullable();
            $table->jsonb('metadata')->default('{}');
            $table->timestampsTz();

            $table->index('notice_id');
        });

        Schema::create('position_reservations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('position_id')->constrained('positions')->cascadeOnDelete();
            $table->string('category', 50);
            $table->string('quota_type', 20)->default('vertical');
            $table->integer('vacancies')->default(0);
            $table->timestampsTz();

            $table->unique(['position_id', 'category']);
            $table->index(['category', 'vacancies']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('position_reservations');
        Schema::dropIfExists('positions');
    }
};
