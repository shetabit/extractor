<?php

namespace Shetabit\Extractor\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:extractor-client')]
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
     * The path used to be built with `base_path()`, which only ever pointed at
     * the right file when the package sat in `vendor/shetabit/extractor`.
     */
    protected function getStub() : string
    {
        return dirname(__DIR__).'/stubs/client.stub';
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getDefaultNamespace($rootNamespace) : string
    {
        return $rootNamespace.'\Http\RemoteRequests\Clients';
    }
}
