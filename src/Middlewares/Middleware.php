<?php

namespace Shetabit\Extractor\Middlewares;

use Closure;
use Shetabit\Extractor\Abstracts\MiddlewareAbstract;
use Shetabit\Extractor\Contracts\RequestInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;

class Middleware extends MiddlewareAbstract
{
    /**
     * Handle request and return suitable response
     *
     * @param RequestInterface $request
     * @param Closure $next
     *
     * @return ResponseInterface
     */
    public function handle(RequestInterface $request, Closure $next) : ?ResponseInterface
    {
        //

        return $next($request);
    }
}
