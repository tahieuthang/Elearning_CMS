<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_video_weekly_progress', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('course_id');
            $table->unsignedBigInteger('course_video_id');
            $table->date('week_start');
            $table->json('watched_ranges');
            $table->unsignedInteger('watched_seconds')->default(0);
            $table->timestamps();
            $table->unique(['customer_id', 'course_video_id', 'week_start'], 'customer_video_week_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_video_weekly_progress');
    }
};
