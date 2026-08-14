<?php

namespace Shetabit\Extractor\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Shetabit\Extractor\Classes\Request;
use Shetabit\Extractor\Providers\ExtractorServiceProvider;

abstract class TestCase extends BaseTestCase
{
    /**
     * Get the package's service providers.
     *
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app) : array
    {
        return [ExtractorServiceProvider::class];
    }

    protected function tearDown() : void
    {
        // The global middlewares are kept in a static property.
        Request::withoutGlobalMiddlewares();

        parent::tearDown();
    }
}
