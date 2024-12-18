<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransactionImportService
{
    private string $baseUrl;
    private string $token;
    private ?string $pageToken = null;
    private int $maxResults = 20;
    private int $retryAttempts = 0;
    private int $maxRetryAttempts = 3;

    public function __construct()
    {
        $this->baseUrl = config('services.simple.url');
        $this->token = config('services.simple.token');
    }

    public function importHistoricalData()
    {
        do {
            try {
                $response = $this->fetchBatch();

                if (!$response->successful()) {
                    Log::error('Failed to fetch transactions', [
                        'status' => $response->status(),
                        'body' => $response->json()
                    ]);

                    // Reset retry counter on success
                    $this->retryAttempts = 0;
                    continue;
                }

                $data = $response->json();
                $this->processBatch($data['tramites']['items']);

                // Update page token for next batch
                $this->pageToken = $data['tramites']['nextPageToken'] ?? null;

                // Reset retry counter on success
                $this->retryAttempts = 0;

                // Add delay between requests to avoid rate limiting
                if ($this->pageToken !== null) {
                    sleep(1); // 1 second delay between requests
                }

                // Log progress
                Log::info('Batch processed', [
                    'count' => count($data['tramites']['items']),
                    'hasMorePages' => !is_null($this->pageToken)
                ]);

            } catch (\Exception $e) {
                Log::error('Error processing batch', [
                    'error' => $e->getMessage(),
                    'pageToken' => $this->pageToken,
                    'attempt' => $this->retryAttempts + 1
                ]);

                if ($this->shouldRetry($e)) {
                    // Add exponential backoff delay
                    $delay = $this->getRetryDelay();
                    sleep($delay);
                    continue;
                }

                throw $e;
            }
        } while ($this->pageToken !== null);
    }

    private function fetchBatch()
    {
        $url = "{$this->baseUrl}/procesos/65/tramites";

        $params = [
            'token' => $this->token,
            'maxResults' => $this->maxResults
        ];

        if ($this->pageToken) {
            $params['pageToken'] = $this->pageToken;
        }

        return Http::get($url, $params);
    }

    private function processBatch(array $transactions)
    {
        foreach ($transactions as $transaction) {
            $this->processTransaction($transaction);
        }
    }

    private function processTransaction(array $data)
    {
        try {
            // Debug log the incoming data
            Log::debug('Processing transaction', [
                'id' => $data['id'],
                'has_datos' => isset($data['datos']),
                'datos_count' => isset($data['datos']) ? count($data['datos']) : 0,
                'raw_data' => $data
            ]);

            // Extract basic data
            $transactionData = $this->transformTransactionData($data);

            // Log transformed data before saving
            Log::info('Transformed transaction data', [
                'transaction_id' => $data['id'],
                'transformed_data' => array_merge(
                    $transactionData,
                    ['full_json' => '[truncated]'] // Avoid logging the full JSON
                )
            ]);

            // Create or update transaction
            $transaction = Transaction::updateOrCreate(
                ['external_id' => $data['id']], // Look up by external ID
                $transactionData
            );

            // Log successful save
            Log::info('Transaction saved successfully', [
                'external_id' => $data['id'],
                'internal_id' => $transaction->id,
                'state_code' => $transaction->state_code,
                'state_name' => $transaction->state_name,
                'city_code' => $transaction->city_code,
                'city_name' => $transaction->city_name
            ]);

            return $transaction;

        } catch (\Exception $e) {
            Log::error('Error processing transaction', [
                'transaction_id' => $data['id'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => array_merge(
                    $data,
                    ['datos' => isset($data['datos']) ? '[truncated]' : null]
                )
            ]);
            throw $e;
        }
    }

    private function transformTransactionData(array $data): array
    {
        try {
            // Convert datos array to collection
            $datos = isset($data['datos']) && is_array($data['datos'])
                ? collect($data['datos'])
                : collect([]);

            // Extract location data FIRST
            $locationData = $this->extractLocationData($datos);

            // Get and validate document type
            $documentType = $this->validateDocumentType(
                $this->getValue($datos, 'tipo_de_documento')
            );

            // Extract user info
            $userInfo = $this->extractUserInfo($data, $datos);

            // Get document number based on type
            $documentNumber = $this->getDocumentNumber($datos, $documentType);

            // Ensure we have a valid JSON string for full_json
            $fullJson = $data;

            // Create base transaction data
            $transformedData = [
                'id' => Str::uuid(),
                'external_id' => $data['id'],
                'proceso_id' => $data['proceso_id'],
                'document_type' => $documentType,
                'person_type' => $this->validatePersonType(
                    $this->getValue($datos, 'tipo_de_persona')
                ),
                'document_number' => $documentNumber,
                'full_name' => $userInfo['full_name'],
                'email' => $userInfo['email'],
                'phone' => $this->getValue($datos, 'n_celular')
                    ?? $this->getValue($datos, 'n_telefono'),

                // Location fields - explicitly set from location data
                'state_code' => $locationData['state_code'],
                'state_name' => $locationData['state_name'],
                'city_code' => $locationData['city_code'],
                'city_name' => $locationData['city_name'],

                'full_json' => $data,
                'status' => $this->validateStatus($data['estado'] ?? 'pendiente'),
                'start_date' => isset($data['fecha_inicio'])
                    ? Carbon::parse($data['fecha_inicio'])
                    : null,
                'end_date' => isset($data['fecha_termino'])
                    ? Carbon::parse($data['fecha_termino'])
                    : null,
                'updated_at' => isset($data['fecha_modificacion'])
                    ? Carbon::parse($data['fecha_modificacion'])
                    : null,
                'sync_status' => 'synced',
                'last_sync_at' => now(),
            ];

            // Log the transformed data for debugging
            Log::debug('Transformation complete', [
                'location_data' => $locationData,
                'transformed_data' => array_intersect_key(
                    $transformedData,
                    array_flip(['state_code', 'state_name', 'city_code', 'city_name'])
                )
            ]);

            return $transformedData;

        } catch (\Exception $e) {
            Log::error('Error in transformTransactionData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            throw $e;
        }
    }


    private function extractLocationData($datos): array
    {
        try {
            // Find the location data in the datos array
            $locationItem = $datos->first(function($item) {
                return isset($item['departamento_y_municipio']) &&
                    is_array($item['departamento_y_municipio']);
            });

            if (!$locationItem || !isset($locationItem['departamento_y_municipio'])) {
                Log::warning('No location data found in datos array');
                return [
                    'state_code' => null,
                    'state_name' => null,
                    'city_code' => null,
                    'city_name' => null
                ];
            }

            $location = $locationItem['departamento_y_municipio'];

            // Add detailed logging to track the extraction
            Log::info('Processing location data', [
                'raw_location' => $location,
                'found_state_code' => $location['cstateCode'] ?? 'not found',
                'found_state_name' => $location['cstateName'] ?? 'not found',
                'found_city_code' => $location['ccityCode'] ?? 'not found',
                'found_city_name' => $location['ccityName'] ?? 'not found'
            ]);

            // Extract and return the location data
            return [
                'state_code' => $location['cstateCode'] ?? null,
                'state_name' => $location['cstateName'] ?? null,
                'city_code' => $location['ccityCode'] ?? null,
                'city_name' => $location['ccityName'] ?? null
            ];
        } catch (\Exception $e) {
            Log::error('Error extracting location data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'datos' => $datos->toArray()
            ]);

            return [
                'state_code' => null,
                'state_name' => null,
                'city_code' => null,
                'city_name' => null
            ];
        }
    }


    private function validateDocumentType(?string $type): string
    {
        // Normalize the input
        $type = strtolower(trim($type ?? ''));

        // Define valid types matching your database ENUM
        $validTypes = [
            'dui' => 'dui',
            'pasaporte' => 'pasaporte',
            'nit' => 'nit',
            'carnet_residente' => 'carnet_residente'
        ];

        return $validTypes[$type] ?? 'dui';
    }

    private function validatePersonType(?string $type): string
    {
        return in_array($type, ['persona_natural', 'persona_juridica'])
            ? $type
            : 'persona_natural';
    }

    private function validateStatus(?string $status): string
    {
        return in_array($status, ['pendiente', 'completado', 'cancelado'])
            ? $status
            : 'pendiente';
    }

    private function getDocumentNumber($datos, string $documentType): ?string
    {
        $getValue = function(string $key) use ($datos) {
            $item = $datos->first(fn($item) => isset($item[$key]));
            return $item[$key] ?? null;
        };

        $number = match($documentType) {
            'dui' => $getValue('dui'),
            'pasaporte' => $getValue('numero_de_documento'),
            'nit' => $getValue('nit'),
            default => null
        };

        // Log document number extraction
        \Log::debug('Document number extraction', [
            'type' => $documentType,
            'number' => $number
        ]);

        return $number;
    }

    private function extractUserInfo(array $data, $datos): array
    {
        $getValue = function(string $key) use ($datos) {
            $item = $datos->first(fn($item) => isset($item[$key]));
            return $item[$key] ?? null;
        };

        // Try to get info from datos first
        $nombreTitular = $getValue('nombre_titular');
        $apellidosTitular = $getValue('apellidos_titular');
        $email = $getValue('email_de_titular');

        // If not found, try etapas
        if ((!$nombreTitular || !$apellidosTitular || !$email) && !empty($data['etapas'])) {
            $userInfo = $data['etapas'][0]['usuario_asignado'] ?? null;
            if ($userInfo) {
                $nombreTitular = $nombreTitular ?: ($userInfo['nombres'] ?? '');
                $apellidosTitular = $apellidosTitular ?: ($userInfo['apellidos'] ?? '');
                $email = $email ?: ($userInfo['email'] ?? null);
            }
        }

        $fullName = trim("{$nombreTitular} {$apellidosTitular}");

        return [
            'full_name' => $fullName ?: null,
            'email' => $email
        ];
    }


    private function parseDate(?string $date): ?Carbon
    {
        return $date ? Carbon::parse($date) : null;
    }


    private function shouldRetry(\Throwable $e): bool
    {
        // Increment attempt counter
        $this->retryAttempts++;

        // Don't retry if we've exceeded max attempts
        if ($this->retryAttempts >= $this->maxRetryAttempts) {
            \Log::warning('Import exceeded maximum retry attempts', [
                'error' => $e->getMessage(),
                'attempts' => $this->retryAttempts
            ]);
            return false;
        }

        // Handle specific exceptions
        if ($e instanceof \GuzzleHttp\Exception\ConnectException) {
            \Log::info('Retrying due to connection error', [
                'attempt' => $this->retryAttempts,
                'error' => $e->getMessage()
            ]);
            return true;
        }

        if ($e instanceof \GuzzleHttp\Exception\ServerException) {
            \Log::info('Retrying due to server error', [
                'attempt' => $this->retryAttempts,
                'error' => $e->getMessage()
            ]);
            return true;
        }

        if ($e instanceof \GuzzleHttp\Exception\ClientException) {
            if ($e->getResponse()->getStatusCode() === 429) {
                \Log::info('Retrying due to rate limiting', [
                    'attempt' => $this->retryAttempts,
                    'error' => $e->getMessage()
                ]);
                return true;
            }
            return false;
        }

        // For unexpected exceptions, retry only on first attempt
        if ($this->retryAttempts === 1) {
            \Log::warning('Retrying unexpected error on first attempt', [
                'error' => $e->getMessage(),
                'class' => get_class($e)
            ]);
            return true;
        }

        \Log::info('Not retrying import', [
            'error' => $e->getMessage(),
            'class' => get_class($e),
            'attempts' => $this->retryAttempts
        ]);

        return false;
    }

    private function getRetryDelay(): int
    {
        // Base delay of 5 seconds with exponential increase
        return min(5 * (2 ** ($this->retryAttempts - 1)), 60); // Max 1 minute
    }

    private function getValue($datos, string $key) {
        $item = $datos->first(fn($item) => isset($item[$key]));
        return $item[$key] ?? null;
    }

}
