<?php

namespace Shetabit\Extractor\Abstracts;

use Shetabit\Extractor\Classes\Request;
use Shetabit\Extractor\Contracts\MicroClientInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;

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
    }

    /**
     * Run client
     *
     * @return ResponseInterface|null
     */
    abstract public function run() : ?ResponseInterface;

    /**
     * Access to request methods directly
     *
     * @param $name
     * @param $params
     * @return mixed
     */
    public function __call($name, $params)
    {
        return call_user_func_array([$this->request, $name], $params);
    }
}
