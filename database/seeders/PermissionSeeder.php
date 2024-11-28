<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'view_users',
                'description' => 'Can view user list'
            ],
            [
                'name' => 'create_users',
                'description' => 'Can create new users'
            ],
            [
                'name' => 'edit_users',
                'description' => 'Can edit existing users'
            ],
            [
                'name' => 'delete_users',
                'description' => 'Can delete users'
            ],
            [
                'name' => 'view_roles',
                'description' => 'Can view roles list'
            ],
            [
                'name' => 'manage_roles',
                'description' => 'Can create, edit, and delete roles'
            ],
            [
                'name' => 'view_transactions',
                'description' => 'Can view transactions'
            ],
            [
                'name' => 'manage_transactions',
                'description' => 'Can manage transactions'
            ]
        ];

        DB::table('permissions')->insert($permissions);
    }
}
