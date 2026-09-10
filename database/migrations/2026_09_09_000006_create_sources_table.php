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
        Schema::create('sources', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('type', 50);
            $table->text('url');
            $table->text('canonical_url')->nullable();
            $table->string('domain', 255);
            $table->string('crawl_method', 50);
            $table->integer('crawl_frequency_minutes')->default(120);
            $table->string('status', 30)->default('active');
            $table->string('trust_level', 30);
            $table->integer('consecutive_failures')->default(0);
            $table->timestampTz('last_crawled_at')->nullable();
            $table->timestampTz('next_crawl_at')->nullable();
            $table->timestampTz('last_success_at')->nullable();
            $table->timestampTz('last_failure_at')->nullable();
            $table->jsonb('configuration')->default('{}');
            $table->jsonb('metadata')->default('{}');
            $table->timestampsTz();

            $table->index(['status', 'next_crawl_at']);
            $table->index('institution_id');
            $table->index('domain');
            $table->index('trust_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
