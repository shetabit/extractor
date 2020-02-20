<?php

namespace Shetabit\Extractor\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MicroClientMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:extractor-client';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new extractor-client';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'MicroClient';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return base_path('vendor/shetabit/extractor/src/Console/stubs/client.stub');
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
        return $rootNamespace.'\Http\RemoteRequests\Clients';
    }
}
