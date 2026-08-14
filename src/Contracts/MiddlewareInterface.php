<?php

namespace Shetabit\Extractor\Contracts;

use Closure;

interface MiddlewareInterface
{
    /**
     * Handle request and return suitable response
     *
     * @param Closure(RequestInterface): (ResponseInterface|null) $next
     */
    public function handle(RequestInterface $request, Closure $next) : ResponseInterface|null;
}
