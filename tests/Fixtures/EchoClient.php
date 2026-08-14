<?php

namespace Shetabit\Extractor\Tests\Fixtures;

use Shetabit\Extractor\Abstracts\MicroClientAbstract;
use Shetabit\Extractor\Contracts\ResponseInterface;

/**
 * A micro client the way `make:extractor-client` generates one.
 */
class EchoClient extends MicroClientAbstract
{
    public function __construct(private readonly string $uri = '/')
    {
        parent::__construct();
    }

    public function run() : ResponseInterface|null
    {
        return $this->request->setUri($this->uri)->fetch();
    }
}
