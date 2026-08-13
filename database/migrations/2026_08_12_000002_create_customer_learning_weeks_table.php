<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_learning_weeks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('customer_id');
            $table->date('week_start');
            $table->timestamp('visit_completed_at')->nullable();
            $table->unsignedInteger('watched_seconds')->default(0);
            $table->timestamp('watch_completed_at')->nullable();
            $table->timestamp('qualified_at')->nullable();
            $table->timestamps();
            $table->unique(['customer_id', 'week_start'], 'customer_learning_week_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_learning_weeks');
    }
};
