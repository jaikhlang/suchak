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
        Schema::create('evidence', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('artifact_id')->constrained('source_artifacts')->cascadeOnDelete();
            $table->foreignUlid('extraction_id')->constrained('artifact_extractions')->cascadeOnDelete();
            $table->foreignUlid('notice_id')->constrained('notices')->cascadeOnDelete();
            $table->string('field_name', 100);
            $table->text('extracted_value');
            $table->integer('page_number')->nullable();
            $table->text('verbatim_text_fragment');
            $table->integer('char_start_offset')->nullable();
            $table->integer('char_end_offset')->nullable();
            $table->jsonb('bounding_box')->nullable();
            $table->decimal('confidence_score', 5, 4)->default(0.0000);
            $table->boolean('is_verified_by_human')->default(false);
            $table->timestampsTz();

            $table->index(['notice_id', 'field_name']);
        });

        Schema::create('notice_revisions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('notice_id')->constrained('notices')->cascadeOnDelete();
            $table->integer('version_number');
            $table->string('change_type', 50);
            $table->text('change_reason')->nullable();
            $table->foreignUlid('triggering_artifact_id')->nullable()->constrained('source_artifacts')->nullOnDelete();
            $table->jsonb('snapshot_data')->default('{}');
            $table->jsonb('diff_data')->default('{}');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('created_at');

            $table->index(['notice_id', 'version_number']);
        });

        Schema::create('moderation_reviews', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('notice_id')->constrained('notices')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 50);
            $table->text('notes')->nullable();
            $table->jsonb('field_corrections')->default('{}');
            $table->timestampTz('reviewed_at');
            $table->timestampTz('created_at');

            $table->index(['notice_id', 'action']);
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('notice_id')->unique()->constrained('notices')->restrictOnDelete();
            $table->string('title', 600);
            $table->string('slug', 650)->unique();
            $table->text('excerpt');
            $table->text('content_html');
            $table->string('status', 30)->default('draft');
            $table->string('seo_title', 150)->nullable();
            $table->string('seo_description', 255)->nullable();
            $table->text('canonical_url');
            $table->timestampTz('published_at')->nullable();
            $table->timestampsTz();

            $table->index('status');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('moderation_reviews');
        Schema::dropIfExists('notice_revisions');
        Schema::dropIfExists('evidence');
    }
};
