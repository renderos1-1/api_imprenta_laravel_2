<?php

namespace App\Services\Export;

use App\Repositories\ReactTransactionRepo;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DepartmentExportService extends BaseExportService
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

    public function generatePDF(array $options = []): array
    {
        try {
            $data = $this->transactionRepo->getTransactionsByDepartment(
                $this->startDate->format('Y-m-d'),
                $this->endDate->format('Y-m-d')
            );

            // Calculate total transactions
            $total = $data->sum('total');

            $formattedData = $data->map(function($item) use ($total) {
                return [
                    'department' => $item['name'],
                    'count' => $item['total'],
                    'percentage' => number_format(($item['total'] / $total) * 100, 2)
                ];
            });

            $pdf = PDF::loadView('exports.department-pdf', [
                'data' => $formattedData,
                'startDate' => $this->startDate->format('d/m/Y'),
                'endDate' => $this->endDate->format('d/m/Y'),
                'total' => $total,
                'generatedAt' => now()->format('d/m/Y H:i:s')
            ]);

            return [
                'filename' => $this->generateFilename('departments', 'pdf'),
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
            $data = $this->transactionRepo->getTransactionsByDepartment(
                $this->startDate->format('Y-m-d'),
                $this->endDate->format('Y-m-d')
            );

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $sheet->setCellValue('A1', 'Departamento');
            $sheet->setCellValue('B1', 'Total Transacciones');
            $sheet->setCellValue('C1', 'Porcentaje');

            // Calculate total
            $total = $data->sum('total');

            // Add data
            $row = 2;
            foreach ($data as $item) {
                $percentage = ($item['total'] / $total) * 100;

                $sheet->setCellValue('A' . $row, $item['name']);
                $sheet->setCellValue('B' . $row, $item['total']);
                $sheet->setCellValue('C' . $row, $this->formatPercentage($percentage));
                $row++;
            }

            // Add total row
            $sheet->setCellValue('A' . $row, 'Total');
            $sheet->setCellValue('B' . $row, $total);
            $sheet->setCellValue('C' . $row, '100%');

            $writer = new Xlsx($spreadsheet);
            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();

            return [
                'filename' => $this->generateFilename('departments', 'xlsx'),
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
            $data = $this->transactionRepo->getTransactionsByDepartment(
                $this->startDate->format('Y-m-d'),
                $this->endDate->format('Y-m-d')
            );

            $output = fopen('php://temp', 'r+');

            // Write headers
            fputcsv($output, ['Departamento', 'Total Transacciones', 'Porcentaje']);

            // Calculate total
            $total = $data->sum('total');

            // Write data
            foreach ($data as $item) {
                $percentage = ($item['total'] / $total) * 100;
                fputcsv($output, [
                    $item['name'],
                    $item['total'],
                    $this->formatPercentage($percentage)
                ]);
            }

            // Add total row
            fputcsv($output, ['Total', $total, '100%']);

            rewind($output);
            $content = stream_get_contents($output);
            fclose($output);

            return [
                'filename' => $this->generateFilename('departments', 'csv'),
                'content' => $content,
                'mime' => 'text/csv'
            ];
        } catch (Exception $e) {
            Log::error('CSV Generation Error: ' . $e->getMessage());
            throw new Exception('Failed to generate CSV file');
        }
    }
}
