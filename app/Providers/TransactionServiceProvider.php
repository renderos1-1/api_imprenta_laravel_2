<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TransactionImportService;

class TransactionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(TransactionImportService::class, function ($app) {
            return new TransactionImportService();
        });
    }

    public function boot()
    {
        //
    }
}
