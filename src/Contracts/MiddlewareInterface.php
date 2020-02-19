<?php

namespace Shetabit\Extractor\Contracts;

use Closure;

interface MiddlewareInterface
{
    /**
     * Handle request and return suitable response
     *
     * @param RequestInterface $request
     * @param Closure $next
     *
     * @return ResponseInterface
     */
    public function handle(RequestInterface $request, Closure $next) : ?ResponseInterface;
}
