<?php

namespace Shetabit\Extractor\Abstracts;

use Closure;
use Shetabit\Extractor\Contracts\MiddlewareInterface;
use Shetabit\Extractor\Contracts\RequestInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;

abstract class MiddlewareAbstract implements MiddlewareInterface, \Stringable
{
    /**
     * Handle request and return suitable response
     *
     * @param Closure(RequestInterface): (ResponseInterface|null) $next
     */
    abstract public function handle(RequestInterface $request, Closure $next) : ResponseInterface|null;

    public function __toString() : string
    {
        return serialize($this);
    }
}
