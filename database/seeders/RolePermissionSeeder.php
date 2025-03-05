<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Get the admin role ID
        $adminRoleId = DB::table('roles')
            ->where('name', 'Administrator')
            ->value('id');

        // Get all permission IDs
        $permissionIds = DB::table('permissions')->pluck('id');

        // Create role_permissions entries for admin role
        $rolePermissions = $permissionIds->map(function ($permissionId) use ($adminRoleId, $now) {
            return [
                'role_id' => $adminRoleId,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        foreach (array_chunk($rolePermissions, 2) as $chunk) {
            DB::table('role_permissions')->insert($chunk);
        }
    }
}
