<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $adminId = Str::uuid();
        DB::table('users')->insert([
            'id' => $adminId,
            'dui' => '00000000-0', // Example DUI for admin
            'password' => Hash::make('admin123'), // Remember to change in production
            'full_name' => 'System Administrator',
            'role_id' => 1, // Admin role
            'is_active' => true,
            'created_at' => now(),
            'created_by' => $adminId, // Self-reference as creator
            'last_modified_by' => $adminId,
            'updated_at' => now()
        ]);

        // Create regular user
        $regularUserId = Str::uuid();
        DB::table('users')->insert([
            'id' => $regularUserId,
            'dui' => '11111111-1', // Example DUI for regular user
            'password' => Hash::make('user123'), // Remember to change in production
            'full_name' => 'Regular User',
            'role_id' => 2, // User role
            'is_active' => true,
            'created_at' => now(),
            'created_by' => $adminId, // Created by admin
            'last_modified_by' => $adminId,
            'updated_at' => now()
        ]);
    }
}
