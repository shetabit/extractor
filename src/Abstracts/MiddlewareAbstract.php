<?php

namespace Shetabit\Extractor\Abstracts;

use Closure;
use Shetabit\Extractor\Contracts\MiddlewareInterface;
use Shetabit\Extractor\Contracts\RequestInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;

abstract class MiddlewareAbstract implements MiddlewareInterface
{
    /**
     * Handle request and return suitable response
     *
     * @param RequestInterface $request
     * @param Closure $next
     *
     * @return ResponseInterface
     */
    abstract public function handle(RequestInterface $request, Closure $next) : ?ResponseInterface;
}
