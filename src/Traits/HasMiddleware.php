<?php

namespace Shetabit\Extractor\Traits;

use Shetabit\Extractor\Contracts\MiddlewareInterface;
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
     * @return MiddlewareInterface
     */
    protected function createMiddlewaresChain()  : ?MiddlewareInterface
    {
        $chain = new Middleware;

        $middlewares = array_diff(
            array_merge(static::$globalMiddlewares, $this->middlewares),
            $this->bannedMiddlewares
        );

        $latest = null;
        foreach ($middlewares as $middleware) {
            if (is_null($latest)) {
                $chain->linkWith($middleware);
            } else {
                $latest->linkWith($middleware);
            }

            $latest = $middleware;
        }

        return $chain;
    }
}