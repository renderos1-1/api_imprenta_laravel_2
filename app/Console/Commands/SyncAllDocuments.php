<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;

class SyncAllDocuments extends Command
{
    protected $signature = 'documents:sync-all
                           {--storage=nas : Storage disk to use}
                           {--batch-size=50 : Number of documents to process in each batch}
                           {--force : Force processing of all documents, even already downloaded ones}';

    protected $description = 'Synchronize all documents in batches until complete';

    public function handle()
    {
        $storageDisk = $this->option('storage');
        $batchSize = (int) $this->option('batch-size');
        $force = $this->option('force') ? '--force' : '';

        $this->info("Starting complete document synchronization to {$storageDisk} storage...");

        $totalProcessed = 0;
        $totalSuccess = 0;

        do {
            // Check how many documents are pending
            $pendingCount = Document::where('status', 'pending')->count();

            if ($pendingCount === 0) {
                $this->info("No more pending documents found.");
                break;
            }

            $this->info("Found {$pendingCount} pending documents. Processing batch of {$batchSize}...");

            // Run the sync command
            $command = "documents:sync --storage={$storageDisk} --limit={$batchSize} {$force}";
            $this->comment("Running: php artisan {$command}");

            $exitCode = $this->call('documents:sync', [
                '--storage' => $storageDisk,
                '--limit' => $batchSize,
                '--force' => $force ? true : false,
            ]);

            if ($exitCode !== 0) {
                $this->error("The documents:sync command failed with exit code {$exitCode}");
                return $exitCode;
            }

            // Check how many were processed in this batch
            $newPendingCount = Document::where('status', 'pending')->count();
            $processedInBatch = $pendingCount - $newPendingCount;
            $totalProcessed += $processedInBatch;

            // Check how many succeeded
            $successInBatch = Document::where('status', 'downloaded')
                ->where('updated_at', '>=', now()->subMinutes(5))
                ->count();
            $totalSuccess += $successInBatch;

            $this->info("Batch completed: {$processedInBatch} processed, {$successInBatch} successful.");

            // Give the system a moment to breathe
            if ($newPendingCount > 0) {
                sleep(1);
            }

        } while ($newPendingCount > 0);

        $this->info("Document synchronization completed.");
        $this->info("Total processed: {$totalProcessed}");
        $this->info("Total successfully downloaded: {$totalSuccess}");

        return 0;
    }
}
