<?php

namespace Shetabit\Extractor\Providers;

use Illuminate\Support\ServiceProvider;
use Shetabit\Extractor\Console\Commands\MicroClientMakeCommand;

class ExtractorServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // load console commands
        $this->loadCommands();
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    protected function loadCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MicroClientMakeCommand::class,
            ]);
        }
    }
}
