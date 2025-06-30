<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissionList = Config::get('constants.permission_list');
        $permissions = [];
        foreach ($permissionList as $permission) {
            $permissions[] = [
                'permission_name' => strtoupper($permission),
                'guard_name' => $permission,
                'description' => 'Permission ' . $permission ,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        DB::table('permissions')->insert($permissions);
    }
}
