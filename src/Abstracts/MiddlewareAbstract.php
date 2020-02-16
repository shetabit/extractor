<?php

namespace Shetabit\Extractor\Abstracts;

use Closure;
use Shetabit\Extractor\Contracts\MiddlewareInterface;
use Shetabit\Extractor\Contracts\RequestInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;

abstract class MiddlewareAbstract implements MiddlewareInterface
{
    /**
     * Next chain
     *
     * @var MiddlewareInterface
     */
    protected $next;

    /**
     * Set next handler
     *
     * @param MiddlewareInterface $next
     *
     * @return MiddlewareInterface
     */
    public function linkWith(MiddlewareInterface $next) : MiddlewareInterface
    {
        $this->next = $next;

        return $next;
    }

    /**
     * Handle request and return suitable response
     *
     * @param RequestInterface $request
     * @param Closure $next
     *
     * @return ResponseInterface
     */
    abstract public function handle(RequestInterface $request, Closure $next) : ?ResponseInterface;

    /**
     * Initialize Handlers chain
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function init(RequestInterface $request, $next) : ?ResponseInterface
    {
        if($this->next) {
            return $this->handle($request, function($request) use ($next) {
                return $this->next->init($request, $next);
            });
        }

        return $this->handle($request, $next);
    }
}
