<?php

namespace Shetabit\Extractor\Tests\Fixtures;

use Closure;
use Shetabit\Extractor\Abstracts\MiddlewareAbstract;
use Shetabit\Extractor\Classes\Response;
use Shetabit\Extractor\Contracts\RequestInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;

/**
 * A middleware that answers on its own instead of letting the request through.
 */
class ShortCircuitMiddleware extends MiddlewareAbstract
{
    public function __construct(
        private readonly string $body = 'from-the-middleware',
        private readonly int $status = 200,
    ) {
    }

    public function handle(RequestInterface $request, Closure $next) : ResponseInterface|null
    {
        return new Response(
            $request->getMethod(),
            $request->getUri(),
            ['X-Answered-By' => ['middleware']],
            $this->body,
            $this->status
        );
    }
}
