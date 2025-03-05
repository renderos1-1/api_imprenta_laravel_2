<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    // Document field names that might contain files
    protected $documentFields = [
        'adjuntar_documento',
        'upload_nrc',
        'recibo_comprobante',
        'cotizacion_para_pago_publi',
        'cotizacion_con_iva_circu'
    ];

    protected $baseUrl = 'https://imprentanacional.ventanilla.simple.sv';

    /**
     * Extract documents from a transaction and create document records
     */
    public function extractDocumentsFromTransaction(Transaction $transaction)
    {
        Log::info("Extracting documents from transaction", ['external_id' => $transaction->external_id]);

        if (!$transaction->full_json) {
            Log::warning("Transaction has no full_json data", ['external_id' => $transaction->external_id]);
            return [];
        }

        $extractedDocuments = [];
        $datos = isset($transaction->full_json['datos']) ? collect($transaction->full_json['datos']) : collect([]);

        foreach ($datos as $item) {
            foreach ($this->documentFields as $field) {
                if (isset($item[$field]) && !empty($item[$field])) {
                    $filename = $item[$field];

                    // Create document record if it doesn't exist
                    $document = Document::firstOrCreate(
                        [
                            'transaction_id' => $transaction->id,
                            'external_transaction_id' => $transaction->external_id,
                            'document_type' => $field,
                            'original_filename' => $filename,
                        ],
                        [
                            'original_url' => $this->generateDocumentUrl($filename),
                            'status' => 'pending'
                        ]
                    );

                    $extractedDocuments[] = $document;

                    Log::info("Document extracted", [
                        'transaction_id' => $transaction->id,
                        'document_type' => $field,
                        'filename' => $filename
                    ]);
                }
            }
        }

        return $extractedDocuments;
    }

    /**
     * Generate a download URL for a document
     */
    public function generateDocumentUrl($filename)
    {
        return "{$this->baseUrl}/uploads/datos/{$filename}";
    }

    /**
     * Download a document and save it to storage
     */
    public function downloadDocument(Document $document, $storageDisk = 'nas')
    {
        if (!$document->original_url) {
            Log::warning("Document has no URL", ['document_id' => $document->id]);
            $document->markAsError("Document has no URL");
            return false;
        }

        try {
            Log::info("Downloading document", [
                'document_id' => $document->id,
                'url' => $document->original_url
            ]);

            // Create storage path
            $transactionFolder = Str::slug($document->external_transaction_id);
            $filename = $document->original_filename;
            $storagePath = "transactions/{$transactionFolder}/{$filename}";

            // Download the file
            $response = Http::get($document->original_url);

            if (!$response->successful()) {
                Log::error("Failed to download document", [
                    'document_id' => $document->id,
                    'status' => $response->status()
                ]);
                $document->markAsError("HTTP error: " . $response->status());
                return false;
            }

            // Store the file
            Storage::disk($storageDisk)->put($storagePath, $response->body());

            // Update document record
            $document->markAsDownloaded($storagePath);

            Log::info("Document downloaded successfully", [
                'document_id' => $document->id,
                'storage_path' => $storagePath
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Error downloading document", [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            $document->markAsError($e->getMessage());
            return false;
        }
    }

    /**
     * Extract and download documents for a transaction
     */
    public function processTransaction(Transaction $transaction, $storageDisk = 'nas')
    {
        $documents = $this->extractDocumentsFromTransaction($transaction);
        $downloadedCount = 0;

        foreach ($documents as $document) {
            if ($document->status !== 'downloaded') {
                $success = $this->downloadDocument($document, $storageDisk);
                if ($success) {
                    $downloadedCount++;
                }
            }
        }

        return $downloadedCount;
    }

    /**
     * Process all pending documents
     */
    public function processAllPendingDocuments($storageDisk = 'nas', $limit = 100)
    {
        $pendingDocuments = Document::where('status', 'pending')
            ->take($limit)
            ->get();

        $processedCount = 0;

        foreach ($pendingDocuments as $document) {
            $success = $this->downloadDocument($document, $storageDisk);
            if ($success) {
                $processedCount++;
            }
        }

        return $processedCount;
    }
}
