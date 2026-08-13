<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_learning_tracking_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('course_id');
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->timestamp('invalidated_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'expires_at'], 'customer_tracking_expiry_index');
            $table->index(['customer_id', 'invalidated_at'], 'customer_tracking_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_learning_tracking_sessions');
    }
};
