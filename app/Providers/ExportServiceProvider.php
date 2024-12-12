<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Export\RevenueExportService;
use App\Repositories\ReactTransactionRepo;
use Illuminate\Filesystem\Filesystem;

class ExportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RevenueExportService::class, function ($app) {
            return new RevenueExportService(
                $app->make(ReactTransactionRepo::class)
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
