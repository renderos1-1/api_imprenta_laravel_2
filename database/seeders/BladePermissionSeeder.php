<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BladePermission;

class BladePermissionSeeder extends Seeder
{
    public function run()
    {
        $bladeTemplates = [
            [
                'name' => 'Dashboard',
                'description' => 'Vista principal del dashboard',
                'route_name' => 'dash'
            ],
            [
                'name' => 'Administración de Usuarios',
                'description' => 'Gestión de usuarios del sistema',
                'route_name' => 'adminuser'
            ],
            [
                'name' => 'Registro de actividad',
                'description' => 'Registro de actividad de usuarios del sistema',
                'route_name' => 'userlog'
            ],
            [
                'name' => 'Estadísticas',
                'description' => 'Vista de estadísticas del sistema',
                'route_name' => 'estadisticas'
            ],
            [
                'name' => 'Transacciones',
                'description' => 'Vista de transacciones del sistema',
                'route_name' => 'transacciones'
            ],

        ];

        foreach ($bladeTemplates as $template) {
            BladePermission::create($template);
        }
    }
}
