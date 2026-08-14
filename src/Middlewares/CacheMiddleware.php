<?php

namespace Shetabit\Extractor\Middlewares;

use Closure;
use Illuminate\Support\Facades\Cache;
use Shetabit\Extractor\Abstracts\MiddlewareAbstract;
use Shetabit\Extractor\Contracts\RequestInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;

class CacheMiddleware extends MiddlewareAbstract
{
    /**
     * Cache constructor
     *
     * @param int $ttl time to live, in seconds
     */
    public function __construct(protected int $ttl = 10)
    {
    }

    /**
     * Handle request and return suitable response
     *
     * @param Closure(RequestInterface): (ResponseInterface|null) $next
     */
    public function handle(RequestInterface $request, Closure $next) : ResponseInterface|null
    {
        $key = $this->getCacheKey($request);

        if ($this->cacheExists($key)) {
            return $this->retrieveFromCache($key);
        }

        return $this->storeInCache($key, $next($request));
    }

    /**
     * Determine if cache exists
     */
    protected function cacheExists(string $key) : bool
    {
        return Cache::has($key);
    }

    /**
     * Store response in cache
     */
    protected function storeInCache(string $key, ResponseInterface|null $response) : ResponseInterface|null
    {
        if ($response !== null) {
            Cache::put($key, $response, $this->ttl);
        }

        return $response;
    }

    /**
     * Retrieve response from cache
     */
    protected function retrieveFromCache(string $key) : ResponseInterface|null
    {
        return Cache::get($key);
    }

    /**
     * Create a unique key for given request
     *
     * The whole request used to be serialized for it, which throws as soon as
     * the request carries an event callback: a closure can not be serialized.
     */
    protected function getCacheKey(RequestInterface $request) : string
    {
        return sha1(implode('|', [
            $request->getMethod(),
            $request->getUri(),
            serialize($request->getOptions()),
        ]));
    }
}
