<?php

namespace App\Services\Export;

use App\Repositories\ReactTransactionRepo;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StageDurationExportService extends BaseExportService
{
    protected ReactTransactionRepo $transactionRepo;

    public function __construct(
        ReactTransactionRepo $transactionRepo,
        ?string $startDate = null,
        ?string $endDate = null
    ) {
        $this->transactionRepo = $transactionRepo;
        parent::__construct($startDate, $endDate);
    }

    protected function analyzeStageData($data)
    {
        // Calculate some useful statistics
        $totalStages = count($data);
        $totalDuration = array_sum(array_column($data->toArray(), 'duration'));
        $averageDuration = $totalDuration / $totalStages;

        // Find bottlenecks (stages taking longer than average)
        $bottlenecks = $data->filter(function($stage) use ($averageDuration) {
            return $stage['duration'] > $averageDuration;
        })->sortByDesc('duration');

        // Find quickest stages
        $quickestStages = $data->filter(function($stage) use ($averageDuration) {
            return $stage['duration'] <= $averageDuration;
        })->sortBy('duration');

        return [
            'totalDuration' => $totalDuration,
            'averageDuration' => $averageDuration,
            'bottlenecks' => $bottlenecks,
            'quickestStages' => $quickestStages,
            'data' => $data
        ];
    }

    public function generatePDF(array $options = []): array
    {
        try {
            $data = $this->transactionRepo->getAverageStageDuration(
                $this->startDate->format('Y-m-d'),
                $this->endDate->format('Y-m-d')
            );

            $analysis = $this->analyzeStageData($data);

            $pdf = PDF::loadView('exports.stage-duration-pdf', [
                'data' => $data,
                'analysis' => $analysis,
                'startDate' => $this->startDate->format('d/m/Y'),
                'endDate' => $this->endDate->format('d/m/Y'),
                'generatedAt' => now()->format('d/m/Y H:i:s')
            ]);

            return [
                'filename' => $this->generateFilename('stage-duration', 'pdf'),
                'content' => $pdf->output(),
                'mime' => 'application/pdf'
            ];
        } catch (Exception $e) {
            Log::error('PDF Generation Error: ' . $e->getMessage());
            throw new Exception('Failed to generate PDF');
        }
    }

    public function generateExcel(array $options = []): array
    {
        try {
            $data = $this->transactionRepo->getAverageStageDuration(
                $this->startDate->format('Y-m-d'),
                $this->endDate->format('Y-m-d')
            );

            $analysis = $this->analyzeStageData($data);

            $spreadsheet = new Spreadsheet();

            // Main Data Sheet
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Duración de Etapas');

            // Headers
            $sheet->setCellValue('A1', 'Etapa del Proceso');
            $sheet->setCellValue('B1', 'Duración Promedio (minutos)');
            $sheet->setCellValue('C1', 'Comparación con Promedio');

            // Style headers
            $sheet->getStyle('A1:C1')->getFont()->setBold(true);
            $sheet->getStyle('A1:C1')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E5E7EB');

            // Add data
            $row = 2;
            foreach ($data as $item) {
                $comparisonToAvg = ($item['duration'] / $analysis['averageDuration']) * 100 - 100;
                $comparisonText = sprintf(
                    '%s%.1f%% %s del promedio',
                    $comparisonToAvg > 0 ? '+' : '',
                    $comparisonToAvg,
                    $comparisonToAvg > 0 ? 'por encima' : 'por debajo'
                );

                $sheet->setCellValue('A' . $row, $item['name']);
                $sheet->setCellValue('B' . $row, round($item['duration'], 2));
                $sheet->setCellValue('C' . $row, $comparisonText);

                // Color code the comparison
                if (abs($comparisonToAvg) > 20) {
                    $color = $comparisonToAvg > 0 ? 'FFE4E4' : 'E4FFE4';
                    $sheet->getStyle('C' . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($color);
                }

                $row++;
            }

            // Add summary section
            $row += 2;
            $sheet->setCellValue('A' . $row, 'Resumen del Análisis');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);

            $row++;
            $sheet->setCellValue('A' . $row, 'Duración Total Promedio:');
            $sheet->setCellValue('B' . $row, round($analysis['totalDuration'], 2) . ' minutos');

            $row++;
            $sheet->setCellValue('A' . $row, 'Duración Promedio por Etapa:');
            $sheet->setCellValue('B' . $row, round($analysis['averageDuration'], 2) . ' minutos');

            // Auto-size columns
            foreach (range('A', 'C') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();

            return [
                'filename' => $this->generateFilename('stage-duration', 'xlsx'),
                'content' => $content,
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ];
        } catch (Exception $e) {
            Log::error('Excel Generation Error: ' . $e->getMessage());
            throw new Exception('Failed to generate Excel file');
        }
    }

    public function generateCSV(array $options = []): array
    {
        try {
            $data = $this->transactionRepo->getAverageStageDuration(
                $this->startDate->format('Y-m-d'),
                $this->endDate->format('Y-m-d')
            );

            $analysis = $this->analyzeStageData($data);

            $output = fopen('php://temp', 'r+');

            // Headers
            fputcsv($output, ['Etapa del Proceso', 'Duración Promedio (minutos)', 'Comparación con Promedio']);

            // Data rows
            foreach ($data as $item) {
                $comparisonToAvg = ($item['duration'] / $analysis['averageDuration']) * 100 - 100;
                $comparisonText = sprintf(
                    '%s%.1f%% %s del promedio',
                    $comparisonToAvg > 0 ? '+' : '',
                    $comparisonToAvg,
                    $comparisonToAvg > 0 ? 'por encima' : 'por debajo'
                );

                fputcsv($output, [
                    $item['name'],
                    round($item['duration'], 2),
                    $comparisonText
                ]);
            }

            // Add summary section
            fputcsv($output, ['']); // Empty line
            fputcsv($output, ['Resumen del Análisis']);
            fputcsv($output, ['Duración Total Promedio:', round($analysis['totalDuration'], 2) . ' minutos']);
            fputcsv($output, ['Duración Promedio por Etapa:', round($analysis['averageDuration'], 2) . ' minutos']);

            rewind($output);
            $content = stream_get_contents($output);
            fclose($output);

            return [
                'filename' => $this->generateFilename('stage-duration', 'csv'),
                'content' => $content,
                'mime' => 'text/csv'
            ];
        } catch (Exception $e) {
            Log::error('CSV Generation Error: ' . $e->getMessage());
            throw new Exception('Failed to generate CSV file');
        }
    }
}
