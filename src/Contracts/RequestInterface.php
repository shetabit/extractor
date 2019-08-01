<?php

namespace Shetabit\Extractor\Contracts;

use Shetabit\Extractor\Abstracts\ResponseAbstract;

interface RequestInterface
{
    /**
     * Set request's URI
     *
     * @param string $url
     * @return mixed
     */
    public function setUri(string $url);

    /**
     * Get request's URI
     *
     * @return string
     */
    public function getUri() : string;

    /**
     * Set request's method (exp: GET, POST, PUT, DELETE, ...)
     *
     * @param string $method
     * @return mixed
     */
    public function setMethod(string $method);

    /**
     * Get request's method
     *
     * @return string
     */
    public function getMethod() : string;

    /**
     * Add a header to request
     *
     * @param string $name
     * @param string $value
     * @return mixed
     */
    public function addHeader(string $name, string $value);

    /**
     * Get header by its name
     *
     * @param string $name
     * @return string
     */
    public function getHeader(string $name) : string;

    /**
     * Get request's headers
     *
     * @return array
     */
    public function getHeaders() : array;

    /**
     * Set Request's deadline (seconds)
     *
     * @param int $timeout
     * @return mixed
     */
    public function setTimeout(int $timeout);

    /**
     * Get request's timeout
     *
     * @return int
     */
    public function getTimeout() : int;

    /**
     * Set request's body
     *
     * @param $body
     * @return mixed
     */
    public function setBody($body); // set request's body

    /**
     * get request's body
     *
     * @return mixed
     */
    public function getBody(); // get request's body

    /**
     * Run request and fetch data
     *
     * @param callable|null $resolve
     * @param callable|null $reject
     * @return ResponseAbstract
     */
    public function fetch(callable $resolve = null, callable $reject = null) : ResponseAbstract;
}
