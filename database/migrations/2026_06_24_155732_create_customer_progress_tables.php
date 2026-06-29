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
        Schema::create('customer_video_progress', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('course_id');
            $table->unsignedBigInteger('course_video_id');
            $table->integer('watched_seconds')->default(0);
            $table->integer('total_seconds')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->unique(['customer_id', 'course_video_id'], 'cust_video_unique');
        });

        Schema::create('customer_quiz_progress', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('course_id');
            $table->unsignedBigInteger('quiz_id');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->unique(['customer_id', 'quiz_id'], 'cust_quiz_unique');
        });

        Schema::create('customer_course_progress', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('course_id');
            $table->integer('progress_percent')->default(0);
            $table->timestamps();

            $table->unique(['customer_id', 'course_id'], 'cust_course_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_course_progress');
        Schema::dropIfExists('customer_quiz_progress');
        Schema::dropIfExists('customer_video_progress');
    }
};
