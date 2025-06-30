<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PostTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('post_tags')->insert([
            ['tag_id' => 4, 'post_id' => 1],
            ['tag_id' => 5, 'post_id' => 1],
            ['tag_id' => 6, 'post_id' => 2],
            ['tag_id' => 7, 'post_id' => 2]
        ]);
    }
}
