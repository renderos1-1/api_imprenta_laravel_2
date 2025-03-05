<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TransactionImportService;

class ImportTransactionData extends Command
{
    protected $signature = 'transactions:import';
    protected $description = 'Import transaction data from external API';

    private TransactionImportService $importService;

    public function __construct(TransactionImportService $importService)
    {
        parent::__construct();
        $this->importService = $importService;
    }

    public function handle()
    {
        try {
            $this->info('Starting transaction import...');
            $this->importService->importHistoricalData();
            $this->info('Transaction import completed successfully.');
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            \Log::error('Transaction import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
