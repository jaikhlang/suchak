<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_subscriptions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('email')->index();
            $table->foreignUlid('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->foreignUlid('institution_id')->nullable()->constrained('institutions')->nullOnDelete();
            $table->string('reservation_category')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->string('verification_token')->unique()->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();

            $table->index(['email', 'state_id', 'reservation_category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_subscriptions');
    }
};
