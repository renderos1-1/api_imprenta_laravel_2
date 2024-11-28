<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Admin role permissions (gets all permissions)
        $adminPermissions = DB::table('permissions')
            ->select('id')
            ->get()
            ->map(function($permission) {
                return [
                    'role_id' => 1, // Admin role ID
                    'permission_id' => $permission->id
                ];
            })
            ->toArray();

        // Regular user permissions
        $userPermissions = [
            [
                'role_id' => 2, // User role ID
                'permission_id' => DB::table('permissions')->where('name', 'view_transactions')->first()->id
            ]
            // Add any other permissions that regular users should have
        ];

        // Insert all role permissions
        DB::table('role_permissions')->insert(array_merge($adminPermissions, $userPermissions));
    }
}
