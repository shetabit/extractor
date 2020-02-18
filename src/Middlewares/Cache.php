<?php

namespace Shetabit\Extractor\Middlewares;

use Closure;
use Shetabit\Extractor\Abstracts\MiddlewareAbstract;
use Shetabit\Extractor\Contracts\RequestInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;

class Cache extends MiddlewareAbstract
{
    /**
     * Time to live
     */
    protected $ttl;

    /**
     * Cache constructor
     *
     * @param int $ttl
     */
    public function __construct($ttl = 10)
    {
        $this->ttl = $ttl;
    }

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
        $key = $this->getCacheKey($request);

        if ($this->cacheExists($key)) {
            return $this->retrieveFromCache($key);
        }

        return $this->storeInCache($key, $next($request));
    }

    /**
     * Determine if cache exists
     *
     * @param string $key
     *
     * @return bool
     */
    protected function cacheExists(string $key) : bool
    {
        return Cache::has($key);
    }

    /**
     * Store response in cache
     *
     * @param string $key
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    protected function storeInCache(string $key, ResponseInterface $response) : ?ResponseInterface
    {
        return Cache::remember($key, $this->ttl, $response);
    }

    /**
     * Retrieve response from cache
     *
     * @param string $key
     *
     * @return ResponseInterface
     */
    protected function retrieveFromCache(string $key) : ?ResponseInterface
    {
        return Cache::get($key);
    }

    /**
     * Create a unique key for given request
     *
     * @param RequestInterface $request
     *
     * @return string
     */
    protected function getCacheKey(RequestInterface $request) : string
    {
        return sha1(serialize($request));
    }

    public function __invoke($params)
    {
        return new static(...$params);
    }
}
