<?php

namespace Shetabit\Extractor\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return ['Shetabit\Extractor\Provider\ExtractorServiceProvider'];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Sms' => 'Shetabit\Extractor\Facade\Extractor',
        ];
    }
}
