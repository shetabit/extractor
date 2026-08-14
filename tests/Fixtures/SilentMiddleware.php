<?php

namespace Shetabit\Extractor\Tests\Fixtures;

use Closure;
use Shetabit\Extractor\Abstracts\MiddlewareAbstract;
use Shetabit\Extractor\Contracts\RequestInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;

/**
 * A middleware that answers with nothing at all, which the contract allows.
 */
class SilentMiddleware extends MiddlewareAbstract
{
    public function handle(RequestInterface $request, Closure $next) : ResponseInterface|null
    {
        return null;
    }
}
