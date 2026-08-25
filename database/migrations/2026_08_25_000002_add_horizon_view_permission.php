<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $timestamp = now();
        $permission = DB::table('permissions')->where('guard_name', 'horizon.view')->first();

        if ($permission === null) {
            $permissionId = DB::table('permissions')->insertGetId([
                'permission_name' => 'HORIZON.VIEW',
                'guard_name' => 'horizon.view',
                'description' => 'Permission horizon.view',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        } else {
            $permissionId = $permission->id;
            DB::table('permissions')->where('id', $permissionId)->update([
                'deleted_at' => null,
                'updated_at' => $timestamp,
            ]);
        }

        $adminRoleId = DB::table('roles')
            ->where('role_name', 'Admin')
            ->whereNull('deleted_at')
            ->value('id');

        if ($adminRoleId === null) {
            return;
        }

        $rolePermission = DB::table('roles_permissions')
            ->where('role_id', $adminRoleId)
            ->where('permission_id', $permissionId)
            ->first();

        if ($rolePermission === null) {
            DB::table('roles_permissions')->insert([
                'role_id' => $adminRoleId,
                'permission_id' => $permissionId,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            return;
        }

        DB::table('roles_permissions')
            ->where('role_id', $adminRoleId)
            ->where('permission_id', $permissionId)
            ->update([
                'deleted_at' => null,
                'updated_at' => $timestamp,
            ]);
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')
            ->where('guard_name', 'horizon.view')
            ->value('id');

        if ($permissionId === null) {
            return;
        }

        $adminRoleId = DB::table('roles')
            ->where('role_name', 'Admin')
            ->value('id');

        if ($adminRoleId !== null) {
            DB::table('roles_permissions')
                ->where('role_id', $adminRoleId)
                ->where('permission_id', $permissionId)
                ->delete();
        }

        DB::table('permissions')->where('id', $permissionId)->update([
            'deleted_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
