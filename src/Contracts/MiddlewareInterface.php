<?php

namespace Shetabit\Extractor\Contracts;

use Closure;

interface MiddlewareInterface
{
    /**
     * Set next handler
     *
     * @param MiddlewareInterface $next
     *
     * @return MiddlewareInterface
     */
    public function linkWith(MiddlewareInterface $next) : MiddlewareInterface;

    /**
     * Initialize Handlers chain
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function init(RequestInterface $request, Closure $next) : ?ResponseInterface;

    /**
     * Handle request and return suitable response
     *
     * @param RequestInterface $request
     * @param Closure $next
     *
     * @return ResponseInterface
     */
    public function handle(RequestInterface $request, Closure $next) : ?ResponseInterface;
}
