<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add indexes used by course category lookups and category searches.
     */
    public function up(): void
    {
        Schema::table('post_categories', function (Blueprint $table): void {
            $table->index('category_name', 'post_categories_category_name_index');
        });

        Schema::table('course_category_pivot', function (Blueprint $table): void {
            $table->index(
                ['post_category_id', 'course_id'],
                'course_category_pivot_category_course_index'
            );
            $table->index(
                ['course_id', 'post_category_id'],
                'course_category_pivot_course_category_index'
            );
        });
    }

    /**
     * Remove the course filtering indexes.
     */
    public function down(): void
    {
        Schema::table('course_category_pivot', function (Blueprint $table): void {
            $table->dropIndex('course_category_pivot_category_course_index');
            $table->dropIndex('course_category_pivot_course_category_index');
        });

        Schema::table('post_categories', function (Blueprint $table): void {
            $table->dropIndex('post_categories_category_name_index');
        });
    }
};
