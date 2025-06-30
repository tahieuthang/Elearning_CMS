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
        $this->call(UserSeeder::class);
        $this->call(TagSeeder::class);
        $this->call(CustomerSeeder::class);
        $this->call(PostCategorySeeder::class);
        $this->call(PostSeeder::class);
        $this->call(PostTagSeeder::class);
        $this->call(PostCategoryPivotSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UserRoleSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(RolePermissionSeeder::class);
    }
}
