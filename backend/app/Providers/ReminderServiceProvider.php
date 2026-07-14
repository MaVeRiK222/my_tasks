<?php

namespace App\Providers;

use App\Services\ReminderService;
use App\Services\TaskService;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ReminderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(ReminderService::class, function (Application $app) {
            return new ReminderService();
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
