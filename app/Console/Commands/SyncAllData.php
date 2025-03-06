<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncAllData extends Command
{
    protected $signature = 'sync:all
                           {--storage=nas : Storage disk to use}
                           {--batch-size=50 : Number of documents to process in each batch}
                           {--reset-errors : Reset documents in error state}';

    protected $description = 'Import new transactions, extract and sync all documents';

    public function handle()
    {
        $startTime = now();
        $storageDisk = $this->option('storage');
        $batchSize = (int) $this->option('batch-size');
        $resetErrors = $this->option('reset-errors');

        try {
            $this->info('Starting complete data synchronization process...');
            Log::info('Starting complete data synchronization', [
                'storage' => $storageDisk,
                'batch_size' => $batchSize
            ]);

            // Step 1: Import new transactions from the API
            $this->info('Importing transactions from API...');
            $exitCode = $this->call('transactions:import');

            if ($exitCode !== 0) {
                $this->error('Transaction import failed!');
                Log::error('Transaction import failed with exit code: ' . $exitCode);
                return $exitCode;
            }

            // Step 2: Extract documents from transactions
            $this->info('Extracting documents from transactions...');
            $exitCode = $this->call('documents:extract');

            if ($exitCode !== 0) {
                $this->error('Document extraction failed!');
                Log::error('Document extraction failed with exit code: ' . $exitCode);
                return $exitCode;
            }

            // Step 3: Reset any documents in error state (if requested)
            if ($resetErrors) {
                $this->info('Resetting documents in error state...');
                try {
                    $affectedRows = DB::table('documents')
                        ->where('status', 'error')
                        ->update([
                            'status' => 'pending',
                            'error_message' => null
                        ]);

                    $this->info("Reset {$affectedRows} documents from error to pending state.");
                    Log::info("Reset {$affectedRows} documents from error to pending state.");
                } catch (\Exception $e) {
                    $this->error('Failed to reset error documents: ' . $e->getMessage());
                    Log::error('Failed to reset error documents', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Continue despite this error
                }
            }

            // Step 4: Sync all documents
            $this->info('Syncing all documents...');
            $exitCode = $this->call('documents:sync-all', [
                '--storage' => $storageDisk,
                '--batch-size' => $batchSize,
            ]);

            if ($exitCode !== 0) {
                $this->error('Document synchronization failed!');
                Log::error('Document synchronization failed with exit code: ' . $exitCode);
                return $exitCode;
            }

            $duration = $startTime->diffInMinutes(now());
            $this->info("Complete data synchronization process finished successfully in {$duration} minutes!");
            Log::info("Complete data synchronization finished", [
                'duration_minutes' => $duration
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error('Synchronization process failed with error: ' . $e->getMessage());
            Log::error('Synchronization process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
