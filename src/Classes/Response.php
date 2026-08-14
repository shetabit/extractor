<?php

namespace Shetabit\Extractor\Classes;

use Shetabit\Extractor\Contracts\ResponseInterface;
use Shetabit\Extractor\Traits\HasParsedUri;

class Response implements ResponseInterface
{
    use HasParsedUri;

    /**
     * Response constructor.
     *
     * @param array<string, mixed> $headers
     */
    public function __construct(
        protected readonly string $method,
        protected readonly string $uri,
        protected readonly array $headers,
        protected readonly string $body,
        protected readonly int $statusCode,
    ) {
    }

    /**
     * Get response's uri
     */
    public function getUri() : string
    {
        return $this->uri;
    }

    /**
     * Get response's method
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * Get response's header by its name
     */
    public function getHeader(string $name) : mixed
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Get all response's header
     *
     * @return array<string, mixed>
     */
    public function getHeaders() : array
    {
        return $this->headers;
    }

    /**
     * Get response's body
     */
    public function getBody() : string
    {
        return $this->body;
    }

    /**
     * get response's status code
     */
    public function getStatusCode() : int
    {
        return $this->statusCode;
    }
}
