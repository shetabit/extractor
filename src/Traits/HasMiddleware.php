<?php

namespace Shetabit\Extractor\Traits;

use Closure;
use Shetabit\Extractor\Contracts\MiddlewareInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;
use Shetabit\Extractor\Middlewares\CacheMiddleware;
use Shetabit\Extractor\Middlewares\Middleware;

trait HasMiddleware
{
    /**
     * Global middlewares
     * this middlewares that will be binded into all requests
     *
     * @var array
     */
    public static $globalMiddlewares = [];

    /**
     * A list of middlewares that's not expected to run
     *
     * @var array
     */
    public $bannedMiddlewares = [];

    /**
     * Middlewares
     *
     * @var array
     */
    protected $middlewares = [];

    /**
     * Add global middlewares
     *
     * @param array $middlewares
     *
     * @return void
     */
    public static function withGlobalMiddlewares(array $middlewares)
    {
        foreach($middlewares as $middleware) {
            $middlewareInstance = ($middleware instanceof MiddlewareInterface) ? $middleware : new $middleware;

            array_push(static::$globalMiddlewares, $middlewareInstance);
        }
    }

    /**
     * Add a middleware that's not expected to run
     *
     * @param MiddlewareInterface $middleware
     *
     * @return $this
     */
    public function withoutMiddleware(MiddlewareInterface $middleware)
    {
        array_push($this->bannedMiddlewares, $middleware);

        return $this;
    }

    /**
     * Bind cache middleware
     *
     * @param $ttl
     *
     * @return $this
     */
    public function cache($ttl = 10)
    {
        $this->middleware(new CacheMiddleware($ttl));

        return $this;
    }

    /**
     * Add middlewares
     *
     * @param MiddlewareInterface $middleware
     *
     * @return $this
     */
    public function middleware(MiddlewareInterface $middleware)
    {
        array_push($this->middlewares, $middleware);

        return $this;
    }

    /**
     * Retrieve a chain of middlewares
     *
     * @return null|ResponseInterface
     */
    protected function invokeMiddlewares($request, $callback)  : ?ResponseInterface
    {
        $middlewares = array_diff(
            array_merge(static::$globalMiddlewares, $this->middlewares),
            $this->bannedMiddlewares
        );

        $middlewares = array_reverse($middlewares);

        $next = function ($request) use ($callback) {
            return $callback($request);
        };

        foreach ($middlewares as $middleware) {
            $next = function ($request) use ($middleware, $next) {
                return $middleware->handle($request, $next);
            };
        }

        return $next($request);
    }
}