<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('roles')->insert([
            [
                'name' => 'Administrator',
                'description' => 'Full system access',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'User',
                'description' => 'Regular user access',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ]);
    }
}
