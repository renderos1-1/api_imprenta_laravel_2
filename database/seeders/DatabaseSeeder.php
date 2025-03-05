<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,    // First, create permissions
            RoleSeeder::class,          // Then create roles
            RolePermissionSeeder::class, // Then assign permissions to roles
            UserSeeder::class,
        ]);
    }
}
