<?php

namespace Shetabit\Extractor\Contracts;

interface RequestInterface
{
    /**
     * Set request's URI
     */
    public function setUri(string $url) : static;

    /**
     * Get request's URI
     */
    public function getUri() : string;

    /**
     * Set request's method (exp: GET, POST, PUT, DELETE, ...)
     */
    public function setMethod(string $method) : static;

    /**
     * Get request's method
     */
    public function getMethod() : string;

    /**
     * Add a header to request
     */
    public function addHeader(string $name, string $value) : static;

    /**
     * Get header by its name
     */
    public function getHeader(string $name) : string|null;

    /**
     * Get request's headers
     *
     * @return array<string, string>
     */
    public function getHeaders() : array;

    /**
     * Set Request's deadline (seconds)
     */
    public function setTimeout(int $timeout) : static;

    /**
     * Get request's timeout
     */
    public function getTimeout() : int;

    /**
     * Follow redirects or not
     */
    public function allowRedirects(bool $allow = true) : static;

    /**
     * Set request's body
     */
    public function setBody(mixed $body) : static;

    /**
     * Get request's body
     */
    public function getBody() : string|null;

    /**
     * The options the request is sent with
     *
     * @return array<string, mixed>
     */
    public function getOptions() : array;

    /**
     * Register the callback that runs after a request succeeded
     *
     * @param callable(ResponseInterface, static): mixed $callback
     */
    public function onSuccess(callable $callback) : static;

    /**
     * Register the callback that runs after a request failed
     *
     * @param callable(ResponseInterface, static): mixed $callback
     */
    public function onError(callable $callback) : static;

    /**
     * Trigger the success event of the request
     */
    public function success(ResponseInterface $response) : static;

    /**
     * Trigger the error event of the request
     */
    public function error(ResponseInterface $response) : static;

    /**
     * Run request and fetch data
     *
     * @param (callable(ResponseInterface, static): mixed)|null $resolve
     * @param (callable(ResponseInterface, static): mixed)|null $reject
     */
    public function fetch(callable|null $resolve = null, callable|null $reject = null) : ResponseInterface;
}
