<?php

namespace App\Providers;

use App\Services\Export\DepartmentExportService;
use App\Services\Export\DocumentTypeExportService;
use App\Services\Export\PersonTypeExportService;
use App\Services\Export\StageDurationExportService;
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

        $this->app->bind(PersonTypeExportService::class, function ($app) {
            return new PersonTypeExportService(
                $app->make(ReactTransactionRepo::class)
            );
        });

        $this->app->bind(DocumentTypeExportService::class, function ($app) {
            return new DocumentTypeExportService(
                $app->make(ReactTransactionRepo::class)
            );
        });

        $this->app->bind(DepartmentExportService::class, function ($app) {
            return new DepartmentExportService(
                $app->make(ReactTransactionRepo::class)
            );
        });

        $this->app->bind(StageDurationExportService::class, function ($app) {
            return new StageDurationExportService(
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
