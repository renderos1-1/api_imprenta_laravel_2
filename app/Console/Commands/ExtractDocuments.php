<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\DocumentService;
use Illuminate\Console\Command;

class ExtractDocuments extends Command
{
    protected $signature = 'documents:extract {--limit=500 : Maximum number of transactions to process}';
    protected $description = 'Extract document references from existing transactions';

    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        parent::__construct();
        $this->documentService = $documentService;
    }

    public function handle()
    {
        $limit = (int) $this->option('limit');

        $this->info("Starting document extraction from transactions...");

        $totalCount = Transaction::count();
        $this->info("Found {$totalCount} total transactions.");

        if ($totalCount === 0) {
            return 0;
        }

        $bar = $this->output->createProgressBar(min($totalCount, $limit));
        $bar->start();

        $processedCount = 0;
        $documentsFound = 0;

        Transaction::take($limit)
            ->chunk(50, function ($transactions) use (&$processedCount, &$documentsFound, $bar) {
                foreach ($transactions as $transaction) {
                    $documents = $this->documentService->extractDocumentsFromTransaction($transaction);
                    $documentsFound += count($documents);

                    $processedCount++;
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info("Processed {$processedCount} transactions. Found {$documentsFound} documents.");

        return 0;
    }
}
