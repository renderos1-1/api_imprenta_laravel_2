<?php

namespace App\Services\Export;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class BaseExportService
{
    protected Carbon $startDate;
    protected Carbon $endDate;

    /**
     * Initialize the export service with date range
     */
    public function __construct(?string $startDate = null, ?string $endDate = null)
    {
        $this->setDateRange($startDate, $endDate);
    }

    /**
     * Set and validate date range
     */
    protected function setDateRange(?string $startDate, ?string $endDate): void
    {
        if ($startDate && $endDate) {
            $this->startDate = Carbon::parse($startDate)->startOfDay();
            $this->endDate = Carbon::parse($endDate)->endOfDay();

            if ($this->startDate->gt($this->endDate)) {
                throw new Exception('Start date cannot be after end date');
            }
        } else {
            // Default to last 30 days if no date range provided
            $this->endDate = Carbon::now()->endOfDay();
            $this->startDate = Carbon::now()->subDays(30)->startOfDay();
        }
    }


    public function export(string $format, string $startDate, string $endDate): array
    {
        // Update date range with the requested dates
        $this->setDateRange($startDate, $endDate);

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


    /**
     * Generate export filename
     */
    protected function generateFilename(string $prefix, string $extension): string
    {
        $dateRange = $this->startDate->format('Y-m-d') . '_to_' . $this->endDate->format('Y-m-d');
        return sprintf('%s_export_%s.%s', $prefix, $dateRange, $extension);
    }

    /**
     * Format currency values
     */
    protected function formatCurrency($value): string
    {
        return number_format((float)$value, 2, '.', ',');
    }

    /**
     * Format percentage values
     */
    protected function formatPercentage($value): string
    {
        return number_format((float)$value, 2) . '%';
    }

    /**
     * Format date values
     */
    protected function formatDate($date): string
    {
        return Carbon::parse($date)->format('d/m/Y');
    }

    /**
     * Abstract methods that must be implemented by child classes
     */
    abstract public function generatePDF(array $options = []): array;
    abstract public function generateExcel(array $options = []): array;
    abstract public function generateCSV(array $options = []): array;

    /**
     * Get date range
     */
    public function getDateRange(): array
    {
        return [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate
        ];
    }
}
