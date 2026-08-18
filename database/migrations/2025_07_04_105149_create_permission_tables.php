<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        throw_if(empty($tableNames), new Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.'));
        throw_if($teams && empty($columnNames['team_foreign_key'] ?? null), new Exception('Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.'));
        
        // Ensure all required table names exist
        $requiredTables = ['permissions', 'roles', 'model_has_permissions', 'model_has_roles', 'role_has_permissions'];
        foreach ($requiredTables as $tableKey) {
            if (!isset($tableNames[$tableKey])) {
                throw new Exception("Error: Missing table name '{$tableKey}' in config/permission.php. Please add it to config/permission.php and run [php artisan config:clear] before migrating.");
            }
        }

        // Check if tables already exist (from old migrations)
        if (!Schema::hasTable($tableNames['permissions'])) {
            Schema::create($tableNames['permissions'], static function (Blueprint $table) {
            // $table->engine('InnoDB');
            $table->bigIncrements('id'); // permission id
            $table->string('name');       // For MyISAM use string('name', 225); // (or 166 for InnoDB with Redundant/Compact row format)
            $table->string('guard_name'); // For MyISAM use string('guard_name', 25);
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
            });
        }

        if (!Schema::hasTable($tableNames['roles'])) {
            Schema::create($tableNames['roles'], static function (Blueprint $table) use ($teams, $columnNames) {
            // $table->engine('InnoDB');
            $table->bigIncrements('id'); // role id
            if ($teams || config('permission.testing')) { // permission.testing is a fix for sqlite testing
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name');       // For MyISAM use string('name', 225); // (or 166 for InnoDB with Redundant/Compact row format)
            $table->string('guard_name'); // For MyISAM use string('guard_name', 25);
            $table->timestamps();
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
            });
        }

        if (!Schema::hasTable($tableNames['model_has_permissions'])) {
            // Check if permissions table exists and get its id column type
            $permissionsTableExists = Schema::hasTable($tableNames['permissions']);
            $useIntegerForPermissionId = false;
            
            if ($permissionsTableExists) {
                // Check if permissions.id is integer (old migration) or bigInteger (new)
                try {
                    $permissionsIdType = DB::select("SHOW COLUMNS FROM `{$tableNames['permissions']}` WHERE Field = 'id'");
                    if (!empty($permissionsIdType) && strpos(strtolower($permissionsIdType[0]->Type), 'bigint') === false) {
                        $useIntegerForPermissionId = true;
                    }
                } catch (\Exception $e) {
                    // Default to bigInteger
                }
            }
            
            Schema::create($tableNames['model_has_permissions'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams, $useIntegerForPermissionId) {
            // Use integer if permissions table has integer id, otherwise use bigInteger
            if ($useIntegerForPermissionId) {
                $table->unsignedInteger($pivotPermission);
            } else {
                $table->unsignedBigInteger($pivotPermission);
            }

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            }

            });
        }

        if (!Schema::hasTable($tableNames['model_has_roles'])) {
            // Check if roles table exists and get its id column type
            $rolesTableExists = Schema::hasTable($tableNames['roles']);
            $useIntegerForRoleId = false;
            
            if ($rolesTableExists) {
                // Check if roles.id is integer (old migration) or bigInteger (new)
                $rolesIdType = DB::select("SHOW COLUMNS FROM `{$tableNames['roles']}` WHERE Field = 'id'");
                if (!empty($rolesIdType) && strpos(strtolower($rolesIdType[0]->Type), 'int') !== false && strpos(strtolower($rolesIdType[0]->Type), 'bigint') === false) {
                    $useIntegerForRoleId = true;
                }
            }
            
            Schema::create($tableNames['model_has_roles'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams, $useIntegerForRoleId) {
            // Use integer if roles table has integer id, otherwise use bigInteger
            if ($useIntegerForRoleId) {
                $table->unsignedInteger($pivotRole);
            } else {
                $table->unsignedBigInteger($pivotRole);
            }

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            } else {
                $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            }
            });
        }

        if (!Schema::hasTable($tableNames['role_has_permissions'])) {
            // Check column types for permissions and roles tables
            $permissionsTableExists = Schema::hasTable($tableNames['permissions']);
            $rolesTableExists = Schema::hasTable($tableNames['roles']);
            $useIntegerForPermissionId = false;
            $useIntegerForRoleIdInPivot = false;
            
            if ($permissionsTableExists) {
                try {
                    $permissionsIdType = DB::select("SHOW COLUMNS FROM `{$tableNames['permissions']}` WHERE Field = 'id'");
                    if (!empty($permissionsIdType) && strpos(strtolower($permissionsIdType[0]->Type), 'bigint') === false) {
                        $useIntegerForPermissionId = true;
                    }
                } catch (\Exception $e) {
                    // Default to bigInteger
                }
            }
            
            if ($rolesTableExists) {
                try {
                    $rolesIdType = DB::select("SHOW COLUMNS FROM `{$tableNames['roles']}` WHERE Field = 'id'");
                    if (!empty($rolesIdType) && strpos(strtolower($rolesIdType[0]->Type), 'bigint') === false) {
                        $useIntegerForRoleIdInPivot = true;
                    }
                } catch (\Exception $e) {
                    // Default to bigInteger
                }
            }
            
            Schema::create($tableNames['role_has_permissions'], static function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission, $useIntegerForPermissionId, $useIntegerForRoleIdInPivot) {
            // Use integer if tables have integer id, otherwise use bigInteger
            if ($useIntegerForPermissionId) {
                $table->unsignedInteger($pivotPermission);
            } else {
                $table->unsignedBigInteger($pivotPermission);
            }
            
            if ($useIntegerForRoleIdInPivot) {
                $table->unsignedInteger($pivotRole);
            } else {
                $table->unsignedBigInteger($pivotRole);
            }

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
            });
        }

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
};
