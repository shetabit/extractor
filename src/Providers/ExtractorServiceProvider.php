<?php

namespace Shetabit\Extractor\Providers;

use Illuminate\Support\ServiceProvider;
use Shetabit\Extractor\Console\Commands\MicroClientMakeCommand;
use Shetabit\Extractor\Console\Commands\MicroClientMiddlewareMakeCommand;

class ExtractorServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot() : void
    {
        // load console commands
        $this->loadCommands();
    }

    /**
     * Register any package services.
     */
    public function register() : void
    {
        //
    }

    /**
     * Load artisan commands
     */
    protected function loadCommands() : void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            MicroClientMakeCommand::class,
            MicroClientMiddlewareMakeCommand::class,
        ]);
    }
}
