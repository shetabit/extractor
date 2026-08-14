<?php

namespace Shetabit\Extractor\Tests\Feature;

use Shetabit\Extractor\Tests\Support\StubServer;
use Shetabit\Extractor\Tests\TestCase;

/**
 * The base of the tests that send their requests over a real connection.
 */
abstract class FeatureTestCase extends TestCase
{
    protected StubServer $server;

    protected function setUp() : void
    {
        parent::setUp();

        $this->server = StubServer::instance();
    }

    /**
     * The answer of the stub server, which echoes the request it received.
     *
     * @return array<string, mixed>
     */
    protected function echoed(string $body) : array
    {
        return (array) json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }
}
