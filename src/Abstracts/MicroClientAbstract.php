<?php

namespace Shetabit\Extractor\Abstracts;

use Shetabit\Extractor\Classes\Request;
use Shetabit\Extractor\Contracts\MicroClientInterface;

abstract class MicroClientAbstract implements MicroClientInterface
{
    /**
     * Request handler
     * Can be used to send request between remote micro services.
     *
     * @var Request
     */
    protected $request;

    /**
     * MicroClientAbstract constructor.
     */
    public function __construct()
    {
        $this->request = new Request;

        $this->run();
    }

    /**
     * Run client
     *
     * @return void
     */
    abstract public function run();
}
