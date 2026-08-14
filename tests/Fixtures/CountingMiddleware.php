<?php

namespace Shetabit\Extractor\Tests\Fixtures;

use Closure;
use Shetabit\Extractor\Abstracts\MiddlewareAbstract;
use Shetabit\Extractor\Contracts\RequestInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;

/**
 * A middleware that writes its name into a shared list when it runs, and adds
 * it to the request as a header.
 */
class CountingMiddleware extends MiddlewareAbstract
{
    /**
     * @var array<int, string>
     */
    public static array $calls = [];

    public function __construct(public readonly string $name = 'a-middleware')
    {
    }

    public function handle(RequestInterface $request, Closure $next) : ResponseInterface|null
    {
        self::$calls[] = $this->name;

        $request->addHeader('X-Middlewares', implode(',', self::$calls));

        return $next($request);
    }
}
