<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TransactionImportService;
use App\Services\DocumentService;

class TransactionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(DocumentService::class, function ($app) {
            return new DocumentService();
        });

        $this->app->singleton(TransactionImportService::class, function ($app) {
            return new TransactionImportService(
                $app->make(DocumentService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
