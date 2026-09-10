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
        Schema::create('institutions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name', 255);
            $table->string('short_name', 50)->nullable();
            $table->string('slug', 255)->unique();
            $table->string('institution_type', 50);
            $table->ulid('parent_id')->nullable();
            $table->foreignUlid('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->text('website_url');
            $table->string('official_domain', 255);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->jsonb('metadata')->default('{}');
            $table->timestampsTz();

            $table->index('institution_type');
            $table->index('official_domain');
            $table->index(['is_verified', 'is_active']);
            $table->index('state_id');
            $table->index('parent_id');
        });

        Schema::table('institutions', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('institutions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
