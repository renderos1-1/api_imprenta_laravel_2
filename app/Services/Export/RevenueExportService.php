<?php

namespace App\Services\Export;

use App\Repositories\ReactTransactionRepo;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RevenueExportService extends BaseExportService
{
    protected Carbon $startDate;
    protected Carbon $endDate;
    protected ReactTransactionRepo $transactionRepo;

    public function __construct(
        ReactTransactionRepo $transactionRepo,
        ?string $startDate = null,
        ?string $endDate = null
    ) {
        $this->transactionRepo = $transactionRepo;

        // Default to last 30 days if no dates provided
        $this->startDate = $startDate ? Carbon::parse($startDate) : Carbon::now()->subDays(30);
        $this->endDate = $endDate ? Carbon::parse($endDate) : Carbon::now();

        $this->validateDateRange();
    }

    protected function validateDateRange(): void
    {
        if ($this->endDate->isBefore($this->startDate)) {
            throw new Exception('End date cannot be before start date');
        }
    }

    protected function formatDate($date): string
    {
        return Carbon::parse($date)->format('Y-m-d');
    }

    protected function formatCurrency($amount): string
    {
        return number_format($amount, 2);
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

    public function export(string $format, string $startDate, string $endDate): array
    {
        $this->startDate = Carbon::parse($startDate);
        $this->endDate = Carbon::parse($endDate);
        $this->validateDateRange();

        switch (strtolower($format)) {
            case 'pdf':
                return $this->generatePDF();
            case 'excel':
                return $this->generateExcel();
            case 'csv':
                return $this->generateCSV();
            default:
                throw new Exception('Unsupported export format');
        }
    }

    public function generatePDF(array $options = []): array
    {
        try {
            $data = $this->transactionRepo->getRevenueData(
                $this->startDate->format('Y-m-d'),
                $this->endDate->format('Y-m-d')
            );

            $pdf = PDF::loadView('exports.revenue-pdf', [
                'data' => $data,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate
            ]);

            $filename = $this->generateFilename('revenue', 'pdf');
            return [
                'filename' => $filename,
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
            $data = $this->transactionRepo->getRevenueData(
                $this->startDate->format('Y-m-d'),
                $this->endDate->format('Y-m-d')
            );

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Headers
            $sheet->setCellValue('A1', 'Date');
            $sheet->setCellValue('B1', 'Revenue');

            // Data
            $row = 2;
            foreach ($data as $item) {
                $sheet->setCellValue('A' . $row, $this->formatDate($item->date));
                $sheet->setCellValue('B' . $row, $this->formatCurrency($item->total));
                $row++;
            }

            $writer = new Xlsx($spreadsheet);
            $filename = $this->generateFilename('revenue', 'xlsx');

            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();

            return [
                'filename' => $filename,
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
            $data = $this->transactionRepo->getRevenueData(
                $this->startDate->format('Y-m-d'),
                $this->endDate->format('Y-m-d')
            );

            $output = fopen('php://temp', 'r+');

            // Headers
            fputcsv($output, ['Date', 'Revenue']);

            // Data
            foreach ($data as $item) {
                fputcsv($output, [
                    $this->formatDate($item->date),
                    $this->formatCurrency($item->total)
                ]);
            }

            rewind($output);
            $content = stream_get_contents($output);
            fclose($output);

            return [
                'filename' => $this->generateFilename('revenue', 'csv'),
                'content' => $content,
                'mime' => 'text/csv'
            ];
        } catch (Exception $e) {
            Log::error('CSV Generation Error: ' . $e->getMessage());
            throw new Exception('Failed to generate CSV file');
        }
    }
}
