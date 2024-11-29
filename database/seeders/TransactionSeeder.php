<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    // El Salvador departments and municipalities
    private array $departamentos = [
        '01' => [
            'name' => 'Ahuachapán',
            'municipalities' => [
                '0101' => 'Ahuachapán',
                '0102' => 'Apaneca',
                '0103' => 'Atiquizaya',
                '0104' => 'Concepción de Ataco'
            ]
        ],
        '02' => [
            'name' => 'Santa Ana',
            'municipalities' => [
                '0201' => 'Santa Ana',
                '0202' => 'Candelaria de la Frontera',
                '0203' => 'Coatepeque',
                '0204' => 'Chalchuapa'
            ]
        ],
        '03' => [
            'name' => 'Sonsonate',
            'municipalities' => [
                '0301' => 'Sonsonate',
                '0302' => 'Acajutla',
                '0303' => 'Armenia',
                '0304' => 'Caluco'
            ]
        ],
        '05' => [
            'name' => 'La Libertad',
            'municipalities' => [
                '0501' => 'Santa Tecla',
                '0502' => 'Antiguo Cuscatlán',
                '0503' => 'Ciudad Arce',
                '0504' => 'Colón'
            ]
        ],
        '06' => [
            'name' => 'San Salvador',
            'municipalities' => [
                '0601' => 'San Salvador',
                '0602' => 'Apopa',
                '0603' => 'Ayutuxtepeque',
                '0604' => 'Cuscatancingo'
            ]
        ]
    ];

    private array $commonNames = [
        'nombres' => [
            'José', 'Juan', 'María', 'Ana', 'Carlos', 'Francisco',
            'Miguel', 'Oscar', 'Carmen', 'Rosa', 'Claudia', 'Mario'
        ],
        'apellidos' => [
            'García', 'Martínez', 'López', 'Hernández', 'González',
            'Pérez', 'Romero', 'Flores', 'Campos', 'Rivera', 'Rivas'
        ]
    ];

    public function run(): void
    {
        $documentTypes = ['dui', 'passport', 'nit'];  // For now just DUI as per requirements
        $personTypes = ['natural', 'juridica'];
        $statuses = ['completado', 'en_proceso', 'cancelado'];

        // Create 50 dummy transactions
        for ($i = 0; $i < 75; $i++) {
            $startDate = Carbon::now()->subDays(rand(1, 30));
            $endDate = Carbon::parse($startDate)->addHours(rand(1, 48));

            // Get random department and municipalityoo
            $deptKey = array_rand($this->departamentos);
            $muniKey = array_rand($this->departamentos[$deptKey]['municipalities']);

            // Generate location data
            $location = [
                'region' => $deptKey,
                'comuna' => $muniKey,
                'cstateCode' => $deptKey,
                'cstateName' => $this->departamentos[$deptKey]['name'],
                'ccityCode' => $muniKey,
                'ccityName' => $this->departamentos[$deptKey]['municipalities'][$muniKey]
            ];

            // Create the base price and calculate derivatives
            $basePrice = rand(100, 500);
            $iva = $basePrice * 0.13; // 13% IVA in El Salvador
            $discount = rand(0, 50);
            $totalPay = $basePrice + $iva - $discount;

            $stages = $this->generateStages($startDate, $endDate);

            DB::table('transactions')->insert([
                'id' => Str::uuid(),
                'document_type' => $documentTypes[array_rand($documentTypes)],
                'person_type' => $personTypes[array_rand($personTypes)],
                'document_number' => sprintf('%08d-%d', rand(10000000, 99999999), rand(0, 9)),
                'full_name' => $this->generateName(),
                'email' => "usuario{$i}@ejemplo.com",
                'phone' => sprintf('7%03d-%04d', rand(0, 999), rand(0, 9999)),
                'full_json' => json_encode([
                    'tramite' => [
                        'id' => rand(9000, 9999),
                        'estado' => $statuses[array_rand($statuses)],
                        'proceso_id' => rand(300, 400),
                        'fecha_inicio' => $startDate->format('Y-m-d H:i:s'),
                        'fecha_modificacion' => $endDate->format('Y-m-d H:i:s'),
                        'fecha_termino' => $endDate->format('Y-m-d H:i:s'),
                        'etapas' => $stages,
                        'datos' => [
                            ['adjuntar_documento' => "documento_" . rand(1000, 9999) . ".pdf"],
                            ['departamento_y_municipio' => $location],
                            ['precio' => $basePrice],
                            ['iva' => $iva],
                            ['descuento' => $discount],
                            ['total_a_pagar' => $totalPay],
                            ['n_telefono' => sprintf('2%03d-%04d', rand(0, 999), rand(0, 9999))],
                            ['n_celular' => sprintf('7%03d-%04d', rand(0, 999), rand(0, 9999))],
                            ['tipo_de_documento' => 'dui'],
                            ['pago_de_tramite' => 'pago_en_ventanilla']
                        ]
                    ]
                ]),
                'created_at' => $startDate,
                'status' => $statuses[array_rand($statuses)],
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        }
    }

    private function generateName(): string
    {
        return $this->commonNames['nombres'][array_rand($this->commonNames['nombres'])] . ' ' .
            $this->commonNames['apellidos'][array_rand($this->commonNames['apellidos'])] . ' ' .
            $this->commonNames['apellidos'][array_rand($this->commonNames['apellidos'])];
    }

    private function generateStages(Carbon $startDate, Carbon $endDate): array
    {
        $stages = [];
        $currentDate = clone $startDate;

        $tasks = [
            ['id' => 9467, 'nombre' => 'Solicitud'],
            ['id' => 9468, 'nombre' => 'Revisión de documentos'],
            ['id' => 9469, 'nombre' => 'Subsanar Observaciones'],
            ['id' => 10025, 'nombre' => 'Revisar observaciones'],
            ['id' => 9470, 'nombre' => 'Cotización'],
            ['id' => 9471, 'nombre' => 'Ciudadano revisa cotización'],
            ['id' => 9476, 'nombre' => 'Adjuntar mandamiento de pago'],
            ['id' => 9474, 'nombre' => 'Revisión de pago'],
            ['id' => 9472, 'nombre' => 'Notificación de publicación (resolutor)']
        ];

        foreach ($tasks as $index => $task) {
            $stageEnd = $currentDate->copy()->addMinutes(rand(15, 60));

            if ($stageEnd > $endDate) {
                $stageEnd = $endDate;
            }

            $stages[] = [
                'id' => rand(60000, 69999),
                'estado' => 'completado',
                'usuario_asignado' => [
                    'usuario' => sprintf('%08d-%d', rand(10000000, 99999999), rand(0, 9)),
                    'email' => 'agente' . rand(1, 10) . '@sistema.com',
                    'nombres' => $this->generateName(),
                ],
                'fecha_inicio' => $currentDate->format('Y-m-d H:i:s'),
                'fecha_termino' => $stageEnd->format('Y-m-d H:i:s'),
                'fecha_modificacion' => $stageEnd->format('Y-m-d H:i:s'),
                'fecha_vencimiento' => null,
                'tarea' => $task
            ];

            $currentDate = $stageEnd;

            if ($currentDate >= $endDate) {
                break;
            }
        }

        return $stages;
    }
}
