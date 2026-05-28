<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spatie Laravel Permission v6 — permission tables migration.
 *
 * Creates:
 *   - permissions
 *   - roles
 *   - model_has_permissions
 *   - model_has_roles
 *   - role_has_permissions
 *
 * Table names and column names are driven by config/permission.php.
 * This migration must run AFTER the users table migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        $tableNames  = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole       = $columnNames['role_pivot_key']       ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $modelMorphKey   = $columnNames['model_morph_key']      ?? 'model_id';

        if (empty($tableNames)) {
            throw new \Exception(
                'Error: config/permission.php not found. '.
                'Please ensure config/permission.php is present before running migrations.'
            );
        }

        // Skip if already migrated (idempotent)
        if (Schema::hasTable($tableNames['permissions'])) {
            return;
        }

        // ---- permissions ------------------------------------------------
        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        // ---- roles ------------------------------------------------------
        Schema::create($tableNames['roles'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        // ---- model_has_permissions --------------------------------------
        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use (
            $tableNames, $columnNames, $pivotPermission, $modelMorphKey
        ) {
            $table->unsignedBigInteger($pivotPermission);
            $table->string('model_type');
            $table->unsignedBigInteger($modelMorphKey);

            $table->index(
                [$modelMorphKey, 'model_type'],
                'model_has_permissions_model_id_model_type_index'
            );

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->primary(
                [$pivotPermission, $modelMorphKey, 'model_type'],
                'model_has_permissions_permission_model_type_primary'
            );
        });

        // ---- model_has_roles --------------------------------------------
        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use (
            $tableNames, $columnNames, $pivotRole, $modelMorphKey
        ) {
            $table->unsignedBigInteger($pivotRole);
            $table->string('model_type');
            $table->unsignedBigInteger($modelMorphKey);

            $table->index(
                [$modelMorphKey, 'model_type'],
                'model_has_roles_model_id_model_type_index'
            );

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(
                [$pivotRole, $modelMorphKey, 'model_type'],
                'model_has_roles_role_model_type_primary'
            );
        });

        // ---- role_has_permissions ---------------------------------------
        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use (
            $tableNames, $pivotRole, $pivotPermission
        ) {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(
                [$pivotPermission, $pivotRole],
                'role_has_permissions_permission_id_role_id_primary'
            );
        });

        // Clear Spatie permission cache
        app('cache')
            ->store(
                config('permission.cache.store') !== 'default'
                    ? config('permission.cache.store')
                    : null
            )
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception(
                'Error: config/permission.php not found. '.
                'Cannot determine table names for rollback.'
            );
        }

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};
