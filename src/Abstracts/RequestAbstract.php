<?php

namespace Shetabit\Extractor\Abstracts;

use Shetabit\Extractor\Classes\Response;
use Shetabit\Extractor\Contracts\RequestInterface;
use GuzzleHttp\Client;

abstract class RequestAbstract implements RequestInterface
{
    /**
     * Request's EndPoint
     *
     * @var string
     */
    protected $uri = '/';

    /**
     * Request's method
     *
     * @var string
     */
    protected $method = 'GET';

    /**
     * Request's custom headers
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Used as request's body
     *
     * @var null
     */
    protected $body = null;

    /**
     * Deadline of each request (seconds)
     *
     * @var float
     */
    protected $timeout = 10.0; // 10 seconds

    /**
     * Set request's uri (endpoint)
     *
     * @param string $url
     * @return $this|mixed
     */
    public function setUri(string $url)
    {
        $this->uri = trim($url);

        return $this;
    }

    /**
     * Get request's endpoint
     *
     * @return string
     */
    public function getUri() : string
    {
        return $this->uri;
    }

    /**
     * Get request's method (example: GET, POST, PUT, PATCH)
     *
     * @param string $method
     * @return $this
     */
    public function setMethod(string $method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get request's method
     *
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * Add custom headers
     *
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function addHeader(string $name, string $value)
    {
        array_push($this->headers, [$name => $value]);

        return $this;
    }

    /**
     * Get header by its name
     *
     * @param string $name
     * @return string
     */
    public function getHeader(string $name) : string
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Retrieve all custom headers
     *
     * @return array
     */
    public function getHeaders() : array {
        return $this->headers;
    }

    /**
     * Set request deadline (seconds)
     *
     * @param int $timeout
     * @return $this
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Get request's deadline (seconds)
     *
     * @return int
     */
    public function getTimeout() : int
    {
        return $this->timeout;
    }

    /**
     * Set request's body
     *
     * @param $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get request's body
     *
     * @return null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Run and fetch data
     *
     * @param callable|null $resolve
     * @param callable|null $reject
     * @return ResponseAbstract
     * @throws \Exception
     */
    public function fetch(callable $resolve = null, callable $reject = null) : ResponseAbstract
    {
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->uri,

            // You can set any number of default request options.
            'timeout'  => $this->getTimeout(),
        ]);

        try {

            $result = $client->request($this->getMethod(), $this->getUri());

            $response = new Response(
                $this->getMethod(),
                $this->getUri(),
                $result->getHeaders(),
                $result->getBody(),
                $result->getStatusCode()
            );

            if (is_callable($resolve)) {
                $resolve($response);
            }

            return $response;

        } catch (\Exception $exception) {

            if (is_callable($resolve)) {
                $reject($exception->getMessage());
            } else {
                throw $exception;
            }

        }
    }

    /**
     * An alias for fetch
     *
     * @param callable|null $resolve
     * @param callable|null $reject
     * @return ResponseAbstract
     * @throws \Exception
     */
    public function send(callable $resolve = null, callable $reject = null)
    {
        return  $this->fetch($resolve, $reject);
    }
}
