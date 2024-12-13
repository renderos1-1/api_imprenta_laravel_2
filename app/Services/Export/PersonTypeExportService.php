<?php

namespace App\Services\Export;

use App\Repositories\ReactTransactionRepo;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;
use Exception;
use PDF;
use Illuminate\Support\Facades\Log;

class PersonTypeExportService extends BaseExportService
{
    protected ReactTransactionRepo $transactionRepo;

    public function __construct(ReactTransactionRepo $transactionRepo)
    {
        $this->transactionRepo = $transactionRepo;
    }

    public function export(string $format, string $startDate, string $endDate): array
    {
        $this->startDate = Carbon::parse($startDate);
        $this->endDate = Carbon::parse($endDate);

        return match($format) {
            'pdf' => $this->generatePDF(),
            'excel' => $this->generateExcel(),
            'csv' => $this->generateCSV(),
            default => throw new Exception('Unsupported format: ' . $format),
        };
    }

    public function generatePDF(array $options = []): array
    {
        try {
            $data = $this->transactionRepo->getDetailedPersonTypeData(
                $this->startDate->format('Y-m-d'),
                $this->endDate->format('Y-m-d')
            );

            $pdf = PDF::loadView('exports.person-type-pdf', [
                'data' => $data,
                'startDate' => $this->startDate->format('Y-m-d'),
                'endDate' => $this->endDate->format('Y-m-d'),
                'generatedAt' => now()->format('Y-m-d H:i:s')
            ]);

            return [
                'filename' => $this->generateFilename('person_type', 'pdf'),
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
            $data = $this->transactionRepo->getDetailedPersonTypeData(
                $this->startDate->format('Y-m-d'),
                $this->endDate->format('Y-m-d')
            );

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set title and metadata
            $sheet->setCellValue('A1', 'Detailed Person Type Report');
            $sheet->mergeCells('A1:F1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

            // Set date range
            $sheet->setCellValue('A2', 'Period:');
            $sheet->setCellValue('B2', $this->startDate->format('Y-m-d') . ' to ' . $this->endDate->format('Y-m-d'));
            $sheet->mergeCells('B2:F2');

            // Headers
            $headers = [
                'A3' => 'Transaction Date',
                'B3' => 'Transaction ID',
                'C3' => 'Document Type',
                'D3' => 'Document Number',
                'E3' => 'Person Type',
                'F3' => 'Status'
            ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getFont()->setBold(true);
            }

            // Data rows
            $row = 4;
            foreach ($data as $item) {
                $sheet->setCellValue('A' . $row, Carbon::parse($item->created_at)->format('Y-m-d H:i:s'));
                $sheet->setCellValue('B' . $row, $item->transaction_id);
                $sheet->setCellValue('C' . $row, strtoupper($item->document_type));
                $sheet->setCellValue('D' . $row, $item->document_number);
                $sheet->setCellValue('E' . $row, $item->person_type === 'persona_natural' ? 'Natural' : 'Jurídica');
                $sheet->setCellValue('F' . $row, ucfirst($item->status));
                $row++;
            }

            // AutoSize columns
            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);

            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();

            return [
                'filename' => $this->generateFilename('person_type', 'xlsx'),
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
            $data = $this->transactionRepo->getDetailedPersonTypeData(
                $this->startDate->format('Y-m-d'),
                $this->endDate->format('Y-m-d')
            );

            $output = fopen('php://temp', 'r+');

            // Add UTF-8 BOM
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Headers
            fputcsv($output, [
                'Transaction Date',
                'Document Type',
                'Document Number',
                'Person Type',
                'Status'
            ]);

            // Data
            foreach ($data as $item) {
                fputcsv($output, [
                    Carbon::parse($item->created_at)->format('Y-m-d H:i:s'),
                    strtoupper($item->document_type),
                    $item->document_number,
                    $item->person_type === 'persona_natural' ? 'Natural' : 'Jurídica',
                    ucfirst($item->status)
                ]);
            }

            rewind($output);
            $content = stream_get_contents($output);
            fclose($output);

            return [
                'filename' => $this->generateFilename('person_type', 'csv'),
                'content' => $content,
                'mime' => 'text/csv'
            ];
        } catch (Exception $e) {
            Log::error('CSV Generation Error: ' . $e->getMessage());
            throw new Exception('Failed to generate CSV file');
        }
    }

    protected function generateFilename(string $prefix, string $extension): string
    {
        return sprintf(
            '%s_%s_to_%s.%s',
            $prefix,
            $this->startDate->format('Y-m-d'),
            $this->endDate->format('Y-m-d'),
            $extension
        );
    }
}
