<?php
// app/Services/PdfExportService.php

namespace App\Services;

use App\Repositories\ReactTransactionRepo;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PdfExportService
{
    protected ReactTransactionRepo $transactionRepo;

    public function __construct(ReactTransactionRepo $transactionRepo)
    {
        $this->transactionRepo = $transactionRepo;
    }

    /**
     * @throws \Exception
     */
    public function generateRevenueReport($startDate, $endDate)
    {
        // Get data from repository
        $data = $this->transactionRepo->getRevenueData($startDate, $endDate);

        // Format the data for the PDF
        $formattedData = $data->map(function ($item) {
            return [
                'date' => Carbon::parse($item->date)->format('d/m/Y'),
                'total' => number_format($item->total, 2)
            ];
        });

        // Calculate totals
        $totalRevenue = $data->sum('total');
        $averageRevenue = $data->avg('total');

        // Generate PDF
        $pdf = PDF::loadView('exports.revenue-report', [
            'data' => $formattedData,
            'startDate' => Carbon::parse($startDate)->format('d/m/Y'),
            'endDate' => Carbon::parse($endDate)->format('d/m/Y'),
            'totalRevenue' => number_format($totalRevenue, 2),
            'averageRevenue' => number_format($averageRevenue, 2),
            'generatedAt' => now()->format('d/m/Y H:i:s')
        ]);

        return $pdf;
    }
}
