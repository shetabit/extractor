<?php

namespace Shetabit\Extractor\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MicroClientMiddlewareMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:extractor-middleware';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new extractor-middleware class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Middleware';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return base_path('vendor/shetabit/extractor/src/Console/stubs/middleware.stub');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\RemoteRequests\Middlewares';
    }
}
