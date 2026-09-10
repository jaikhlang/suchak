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
        Schema::create('source_artifacts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('source_id')->constrained('sources')->cascadeOnDelete();
            $table->foreignUlid('crawl_run_id')->constrained('crawl_runs')->cascadeOnDelete();
            $table->string('type', 50);
            $table->text('url');
            $table->text('canonical_url');
            $table->string('content_hash', 64);
            $table->string('etag', 255)->nullable();
            $table->string('last_modified_header', 255)->nullable();
            $table->string('mime_type', 100);
            $table->integer('http_status')->default(200);
            $table->string('storage_disk', 50)->default('s3');
            $table->text('storage_path');
            $table->bigInteger('file_size_bytes');
            $table->string('title', 500)->nullable();
            $table->timestampTz('retrieved_at');
            $table->jsonb('metadata')->default('{}');
            $table->timestampsTz();

            $table->unique(['source_id', 'content_hash']);
            $table->index('retrieved_at');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('source_artifacts');
    }
};
