<?php

namespace App\Console\Commands;

use App\Services\TransactionImportService;
use Illuminate\Console\Command;

class ImportHistoricalTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:import-historical';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import historical transactions from Simple API';

    /**
     * Execute the console command.
     */
    public function handle(TransactionImportService $importService)
    {
        $this->info('Starting historical transaction import...');

        try {
            $importService->importHistoricalData();
            $this->info('Import completed successfully!');
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
