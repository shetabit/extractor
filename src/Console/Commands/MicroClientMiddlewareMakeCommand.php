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
    protected $name = 'make:micro-client-middleware';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new micro-client-middleware class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'MicroClientMiddleware';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return base_path('vendor/shetabit/extractor/src/Console/stubs/micro-client-middleware.stub');
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
        return $rootNamespace.'\Http\MicroClients\Middlewares';
    }
}
