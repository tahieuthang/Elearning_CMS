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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('course_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('quiz_id');
            $table->text('question_text');
            $table->string('type')->default('single'); // 'single' or 'multiple'
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('quiz_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('question_id');
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        if (!Schema::hasColumn('course_videos', 'order')) {
            Schema::table('course_videos', function (Blueprint $table) {
                $table->integer('order')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_options');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
        
        if (Schema::hasColumn('course_videos', 'order')) {
            Schema::table('course_videos', function (Blueprint $table) {
                $table->dropColumn('order');
            });
        }
    }
};
