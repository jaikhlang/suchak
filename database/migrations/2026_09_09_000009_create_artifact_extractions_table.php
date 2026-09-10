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
        Schema::create('artifact_extractions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('artifact_id')->constrained('source_artifacts')->cascadeOnDelete();
            $table->string('method', 50);
            $table->string('status', 30)->default('processing');
            $table->longText('raw_text')->nullable();
            $table->longText('clean_text')->nullable();
            $table->integer('page_count')->nullable();
            $table->decimal('confidence_score', 5, 4)->nullable();
            $table->string('processor_name', 100);
            $table->timestampTz('started_at');
            $table->timestampTz('finished_at')->nullable();
            $table->jsonb('metadata')->default('{}');
            $table->timestampsTz();

            $table->index(['artifact_id', 'status']);
            $table->index('method');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artifact_extractions');
    }
};
