<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Console\Command;

class DocumentsSync extends Command
{
    protected $signature = 'documents:sync
                            {--storage=nas : Storage disk to use}
                            {--limit=1000 : Maximum number of documents to process}
                            {--force : Force processing of all documents, even already downloaded ones}';

    protected $description = 'Synchronize documents from the Simple API to local storage';

    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        parent::__construct();
        $this->documentService = $documentService;
    }

    public function handle()
    {
        $storageDisk = $this->option('storage');
        $limit = (int) $this->option('limit');
        $force = $this->option('force');

        $this->info("Starting document sync to {$storageDisk} storage...");

        if ($force) {
            $this->processAllDocuments($storageDisk, $limit);
        } else {
            $this->processPendingDocuments($storageDisk, $limit);
        }

        $this->info("Document sync completed.");

        return 0;
    }

    protected function processPendingDocuments($storageDisk, $limit)
    {
        $pendingCount = Document::where('status', 'pending')->count();
        $this->info("Found {$pendingCount} pending documents.");

        if ($pendingCount === 0) {
            return;
        }

        $bar = $this->output->createProgressBar(min($pendingCount, $limit));
        $bar->start();

        $processedCount = 0;
        $successCount = 0;

        Document::where('status', 'pending')
            ->take($limit)
            ->chunk(10, function ($documents) use (&$processedCount, &$successCount, $bar, $storageDisk) {
                foreach ($documents as $document) {
                    $success = $this->documentService->downloadDocument($document, $storageDisk);

                    if ($success) {
                        $successCount++;
                    }

                    $processedCount++;
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info("Processed {$processedCount} pending documents. Successfully downloaded {$successCount}.");
    }

    protected function processAllDocuments($storageDisk, $limit)
    {
        $totalCount = Document::count();
        $this->info("Found {$totalCount} total documents (including already downloaded).");

        if ($totalCount === 0) {
            return;
        }

        $bar = $this->output->createProgressBar(min($totalCount, $limit));
        $bar->start();

        $processedCount = 0;
        $successCount = 0;

        Document::take($limit)
            ->chunk(10, function ($documents) use (&$processedCount, &$successCount, $bar, $storageDisk) {
                foreach ($documents as $document) {
                    if ($document->status === 'downloaded') {
                        // Skip already downloaded unless there's no storage path
                        if (!empty($document->storage_path)) {
                            $processedCount++;
                            $bar->advance();
                            continue;
                        }
                    }

                    $success = $this->documentService->downloadDocument($document, $storageDisk);

                    if ($success) {
                        $successCount++;
                    }

                    $processedCount++;
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info("Processed {$processedCount} documents. Successfully downloaded {$successCount}.");
    }
}
