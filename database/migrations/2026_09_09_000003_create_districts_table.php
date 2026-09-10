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
        Schema::create('districts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('state_id')->constrained('states')->restrictOnDelete();
            $table->string('name', 100);
            $table->string('slug', 120);
            $table->timestampsTz();

            $table->unique(['state_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
