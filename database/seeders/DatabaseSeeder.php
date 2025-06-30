<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            TagSeeder::class,
            CustomerSeeder::class,
            PostCategorySeeder::class,
            PostSeeder::class,
            PostTagSeeder::class,
            PostCategoryPivotSeeder::class,
            RoleSeeder::class,
            UserRoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
        ]);
    }
}
