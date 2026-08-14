<?php

namespace Shetabit\Extractor\Contracts;

interface ResponseInterface
{
    /**
     * Get response's uri
     */
    public function getUri() : string;

    /**
     * Get response's method
     */
    public function getMethod() : string;

    /**
     * Get response's header by its name
     */
    public function getHeader(string $name) : mixed;

    /**
     * Get all response's header
     *
     * @return array<string, mixed>
     */
    public function getHeaders() : array;

    /**
     * Get response's body
     */
    public function getBody() : string;

    /**
     * get response's status code
     */
    public function getStatusCode() : int;
}
