<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('posts')->insert([
            [
                'title' => 'Generating Migrations',
                'description' => 'You should commit your database schema file to source control so that other new developers on your team may quickly create your application initial database structure',
                'content' => 'You should commit your database schema file to source control so that other new developers on your team may quickly create your applications initial database structure',
                'thumbnail' => 'https://picsum.photos/200',
                'status' => 1,
                'created_at' => '2022-09-07 12:23:00',
                'updated_at' => '2022-09-07 12:23:00',
            ],
            [
                'title' => 'Squashing Migrations',
                'description' => 'You may use the make:migration Artisan command to generate a database migration',
                'content' => 'You may use the make:migration Artisan command to generate a database migration. The new migration will be placed in your database/migrations directory. Each migration filename',
                'thumbnail' => 'https://picsum.photos/300',
                'status' => 1,
                'created_at' => '2022-09-08 12:23:00',
                'updated_at' => '2022-09-08 12:23:00',
            ],
            [
                'title' => 'Introduction',
                'description' => 'Migrations are like version control for your database, allowing your team to define and share the application database schema definition',
                'content' => 'You may use the make:migration Artisan command to generate a database migration. The new migration will be placed in your database/migrations directory. Each migration filename',
                'thumbnail' => 'https://picsum.photos/400',
                'status' => 2,
                'created_at' => '2022-09-10 12:23:00',
                'updated_at' => '2022-09-10 12:23:00',
            ],
        ]);
    }
}
