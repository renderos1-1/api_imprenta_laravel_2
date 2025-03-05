<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $permissions = [
            [
                'name' => 'view-dashboard',
                'display_name' => 'Dashboard',
                'description' => 'Access to dashboard',
                'group' => 'dashboard',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'manage-users',
                'display_name' => 'Administración de Usuarios',
                'description' => 'Manage users',
                'group' => 'users',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'view-transactions',
                'display_name' => 'Transacciones',
                'description' => 'View transactions',
                'group' => 'transactions',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'view-statistics',
                'display_name' => 'Estadísticas',
                'description' => 'Access statistics',
                'group' => 'statistics',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'export-data',
                'display_name' => 'Exportar Datos',
                'description' => 'Export data from any view',
                'group' => 'export',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'view-activity',
                'display_name' => 'Registro de actividad',
                'description' => 'View activity logs',
                'group' => 'activity',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach (array_chunk($permissions, 2) as $chunk) {
            DB::table('permissions')->insert($chunk);
        }
    }
}
