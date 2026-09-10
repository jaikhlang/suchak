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
        Schema::create('crawl_runs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('source_id')->constrained('sources')->cascadeOnDelete();
            $table->string('status', 30)->default('queued');
            $table->timestampTz('started_at');
            $table->timestampTz('finished_at')->nullable();
            $table->integer('items_discovered')->default(0);
            $table->integer('items_fetched')->default(0);
            $table->integer('items_failed')->default(0);
            $table->integer('http_status')->nullable();
            $table->string('error_code', 100)->nullable();
            $table->text('error_message')->nullable();
            $table->jsonb('metadata')->default('{}');
            $table->timestampsTz();

            $table->index(['source_id', 'status']);
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crawl_runs');
    }
};
