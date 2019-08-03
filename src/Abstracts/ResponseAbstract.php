<?php

namespace Shetabit\Extractor\Abstracts;

use Shetabit\Extractor\Contracts\ResponseInterface;

abstract class ResponseAbstract implements ResponseInterface
{
    /**
     * Response's uri
     *
     * @var string
     */
    protected $uri;

    /**
     * Response's method
     *
     * @var string
     */
    protected $method;

    /**
     * Response's header
     *
     * @var array
     */
    protected $headers;

    /**
     * Response's body
     *
     * @var string
     */
    protected $body;

    /**
     * Response's status (http status code)
     *
     * @var int
     */
    protected $statusCode;

    public function __construct(string $method, string $uri, array $headers, string $body, int $statusCode)
    {
        $this->method= $method;
        $this->uri = $uri;
        $this->headers = $headers;
        $this->body = $body;
        $this->statusCode = $statusCode;
    }

    /**
     * Get response's uri
     *
     * @return string
     */
    public function getUri() : string
    {
        return $this->uri;
    }

    /**
     * Get response's method
     *
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * Get response's header by its name
     *
     * @param string $name
     * @return mixed|string|null
     */
    public function getHeader(string $name)
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Get all response's header
     *
     * @return array
     */
    public function getHeaders() : array
    {
        return $this->headers;
    }

    /**
     * Get response's body
     *
     * @return string
     */
    public function getBody() : string
    {
        return $this->body;
    }

    /**
     * get response's status code
     *
     * @return int
     */
    public function getStatusCode() : int
    {
        return $this->statusCode;
    }
}
