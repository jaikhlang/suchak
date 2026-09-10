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
        Schema::create('application_details', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('notice_id')->unique()->constrained('notices')->cascadeOnDelete();
            $table->string('application_mode', 30)->default('online');
            $table->text('apply_url')->nullable();
            $table->text('official_notification_pdf_url')->nullable();
            $table->integer('general_fee')->default(0);
            $table->integer('reserved_fee')->default(0);
            $table->integer('female_fee')->default(0);
            $table->boolean('is_exempted_for_sc_st')->default(false);
            $table->boolean('is_exempted_for_female')->default(false);
            $table->boolean('is_exempted_for_pwbd')->default(false);
            $table->text('offline_postal_address')->nullable();
            $table->string('postal_pincode', 10)->nullable();
            $table->text('instructions')->nullable();
            $table->jsonb('metadata')->default('{}');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_details');
    }
};
