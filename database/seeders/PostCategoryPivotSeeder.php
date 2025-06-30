<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PostCategoryPivotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('post_category_pivot')->insert([
            ['post_id' => 1, 'post_category_id' => 1],
            ['post_id' => 1, 'post_category_id' => 2],
            ['post_id' => 2, 'post_category_id' => 1],
            ['post_id' => 3, 'post_category_id' => 1],
        ]);
    }
}
