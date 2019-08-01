<?php

namespace Shetabit\Extractor\Contracts;

interface ResponseInterface
{
    /**
     * Get response's uri
     *
     * @return string
     */
    public function getUri() : string;

    /**
     * Get response's method
     *
     * @return string
     */
    public function getMethod() : string;

    /**
     * Get response's header by its name
     *
     * @param string $name
     * @return string
     */
    public function getHeader(string $name) : string;

    /**
     * Get all response's header
     *
     * @return array
     */
    public function getHeaders() : array;

    /**
     * Get response's body
     *
     * @return string
     */
    public function getBody() : string;

    /**
     * get response's status code
     *
     * @return int
     */
    public function getStatusCode() : int;
}
