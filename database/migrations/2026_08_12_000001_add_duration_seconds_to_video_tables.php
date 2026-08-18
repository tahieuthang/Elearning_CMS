<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_videos', function (Blueprint $table) {
            $table->unsignedInteger('duration_seconds')->nullable()->after('video_thumbnail');
        });
        Schema::table('video_uploadings', function (Blueprint $table) {
            $table->unsignedInteger('duration_seconds')->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('video_uploadings', function (Blueprint $table) {
            $table->dropColumn('duration_seconds');
        });
        Schema::table('course_videos', function (Blueprint $table) {
            $table->dropColumn('duration_seconds');
        });
    }
};
