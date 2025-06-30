<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roleAdmin = DB::table('roles')->where('role_name', 'Admin')->first();
        if ($roleAdmin) {
            $rolePermissions = DB::table('permissions')
                                ->where('guard_name', 'role.list')
                                ->orWhere('guard_name', 'role.edit')
                                ->orWhere('guard_name', 'role.delete')
                                ->orWhere('guard_name', 'role.create')->get();
            $dataRolePermissions = [];
            foreach($rolePermissions as $permission) {
                $dataRolePermissions[] = [
                    'role_id' => $roleAdmin->id,
                    'permission_id' => $permission->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('roles_permissions')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::table('roles_permissions')->insert($dataRolePermissions);
        }
    }
}
