<?php

namespace Shetabit\Extractor\Traits;

use Shetabit\Extractor\Contracts\MiddlewareInterface;
use Shetabit\Extractor\Contracts\RequestInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;
use Shetabit\Extractor\Middlewares\CacheMiddleware;

trait HasMiddleware
{
    /**
     * Global middlewares
     * this middlewares that will be binded into all requests
     *
     * @var array<int, MiddlewareInterface>
     */
    public static array $globalMiddlewares = [];

    /**
     * A list of middlewares that's not expected to run
     *
     * @var array<int, MiddlewareInterface>
     */
    public array $bannedMiddlewares = [];

    /**
     * Middlewares
     *
     * @var array<int, MiddlewareInterface>
     */
    protected array $middlewares = [];

    /**
     * Add global middlewares
     *
     * @param array<int, MiddlewareInterface|class-string<MiddlewareInterface>> $middlewares
     */
    public static function withGlobalMiddlewares(array $middlewares) : void
    {
        foreach ($middlewares as $middleware) {
            static::$globalMiddlewares[] = $middleware instanceof MiddlewareInterface
                ? $middleware
                : new $middleware();
        }
    }

    /**
     * Throw the global middlewares away.
     */
    public static function withoutGlobalMiddlewares() : void
    {
        static::$globalMiddlewares = [];
    }

    /**
     * Add a middleware that's not expected to run
     */
    public function withoutMiddleware(MiddlewareInterface $middleware) : static
    {
        $this->bannedMiddlewares[] = $middleware;

        return $this;
    }

    /**
     * Bind cache middleware
     *
     * @param int $ttl time to live, in seconds
     */
    public function cache(int $ttl = 10) : static
    {
        return $this->middleware(new CacheMiddleware($ttl));
    }

    /**
     * Add middlewares
     */
    public function middleware(MiddlewareInterface $middleware) : static
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * The middlewares that run for this request, the global ones first.
     *
     * @return array<int, MiddlewareInterface>
     */
    public function getMiddlewares() : array
    {
        $middlewares = array_merge(static::$globalMiddlewares, $this->middlewares);

        return array_values(array_filter(
            $middlewares,
            /**
             * A banned middleware is recognized by what it is, not by being the
             * very same object: `withoutMiddleware(new SomeMiddleware())` bans
             * the middleware of that class with those settings.
             */
            fn (MiddlewareInterface $middleware): bool => !in_array($middleware, $this->bannedMiddlewares, false)
        ));
    }

    /**
     * Retrieve a chain of middlewares
     *
     * @param callable(RequestInterface): (ResponseInterface|null) $callback
     */
    protected function invokeMiddlewares(RequestInterface $request, callable $callback) : ResponseInterface|null
    {
        $next = fn (RequestInterface $request): ResponseInterface|null => $callback($request);

        foreach (array_reverse($this->getMiddlewares()) as $middleware) {
            $next = fn (RequestInterface $request): ResponseInterface|null => $middleware->handle($request, $next);
        }

        return $next($request);
    }
}
