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
        Schema::create('notices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('institution_id')->constrained('institutions')->restrictOnDelete();
            $table->foreignUlid('source_id')->constrained('sources')->restrictOnDelete();
            $table->foreignUlid('source_artifact_id')->constrained('source_artifacts')->restrictOnDelete();
            $table->string('notice_type', 50)->default('recruitment');
            $table->string('title', 500);
            $table->string('slug', 600)->unique();
            $table->string('reference_number', 255)->nullable();
            $table->text('summary')->nullable();
            $table->string('status', 30)->default('discovered');
            $table->decimal('confidence_score', 5, 4)->default(0.0000);
            $table->boolean('is_corrigendum')->default(false);
            $table->ulid('parent_notice_id')->nullable();
            $table->timestampTz('published_at')->nullable();
            $table->timestampTz('application_start_at')->nullable();
            $table->timestampTz('application_end_at')->nullable();
            $table->timestampTz('fee_payment_end_at')->nullable();
            $table->timestampTz('correction_window_end_at')->nullable();
            $table->string('tentative_exam_date_text', 255)->nullable();
            $table->timestampTz('exam_start_at')->nullable();
            $table->timestampTz('exam_end_at')->nullable();
            $table->text('canonical_source_url');
            $table->integer('total_vacancies')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestampTz('first_seen_at');
            $table->timestampTz('last_seen_at');
            $table->jsonb('metadata')->default('{}');
            $table->timestampsTz();

            $table->index(['status', 'application_end_at']);
            $table->index(['institution_id', 'status']);
            $table->index('reference_number');
            $table->index('parent_notice_id');
        });

        Schema::table('notices', function (Blueprint $table) {
            $table->foreign('parent_notice_id')->references('id')->on('notices')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('notices')) {
            Schema::table('notices', function (Blueprint $table) {
                $table->dropForeign(['parent_notice_id']);
            });
            Schema::dropIfExists('notices');
        }
    }
};
