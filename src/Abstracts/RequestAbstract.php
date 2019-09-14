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
     * Used as request's multipart data
     *
     * @var array
     */
    protected $multipartData = [];

    /**
     * Used to send request's params similar to forms
     *
     * @var array
     */
    protected $formParams= [];

    /**
     * Request's query
     *
     * @var array
     */
    protected $queries = [];

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
        $this->headers = array_merge($this->headers, [$name => $value]);

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
     * add form data
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function addFormParam($name, $value)
    {
        $this->formParams = array_merge($this->formParams, [$name => $value]);

        return $this;
    }

    /**
     * retrieve multipart data
     *
     * if name is empty , all data will returned
     *
     * @param $name
     * @return mixed|null
     */
    public function getFormParam($name)
    {
        return $this->formParams[$name] ?? null;
    }

    /**
     * retrieve all form params
     *
     * @return array
     */
    public function getFormParams()
    {
        return $this->formParams;
    }

    /**
     * add form data
     *
     * @param $name
     * @param $value
     * @param array $headers
     * @return $this
     */
    public function addMultiparData($name, $value, array $headers = [])
    {
        $data = [
            'name' => $name,
            'contents' => $value,
            'headers' => $headers
        ];

        array_push($this->multipartData, $data);

        return $this;
    }


    /**
     * retrieve multipart data
     *
     * if name is empty , all data will returned
     *
     * @param $name
     * @return mixed|null
     */
    public function getMultipartData($name = null)
    {
        return empty($name) ? $this->multipartData : ($this->multipartData[$name] ?? null);
    }

    /**
     * add query
     *
     * @param $name
     * @param $value
     * @param array $headers
     * @return $this
     */
    public function addQuery($name, $value)
    {
        $this->queries = array_merge($this->queries, [$name => $value]);

        return $this;
    }

    /**
     * retrieve multipart data
     *
     * if name is empty , all data will returned
     *
     * @param $name
     * @return mixed|null
     */
    public function getQuery($name)
    {
        return $this->queries[$name] ?? null;
    }

    /**
     * Get request's queries
     *
     * @return array
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * Generate options
     */
    protected function getOptions()
    {
        $options = [
            'http_errors' => false,
            'body' => $this->getBody(),
            'query' => $this->getQueries(),
            'headers' => $this->getHeaders(),
        ];

        /*
         * we cant use formParams and MultipartData at the same time.
         * this part selects one of them.
         */
        if (!empty($this->getFormParams())) {
            $options['form_params'] = $this->getFormParams();
        } else if (!empty($this->getMultipartData())) {
            $options['multipart'] = $this->getMultipartData();
        }

        return $options;
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

        $result = $client->request($this->getMethod(), $this->getUri(), $this->getOptions());

        $response = new Response(
            $this->getMethod(),
            $this->getUri(),
            $result->getHeaders(),
            $result->getBody(),
            $result->getStatusCode()
        );

        if ($response->getStatusCode() == 200) { // handle 200 OK response
            if (is_callable($resolve)) {
                $resolve($response);
            }
        } else {
            if (is_callable($reject)) { // handle responses has error status
                $reject($response);
            }
        }

        return $response;
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

    /**
     * Run and fetch data asynchronously
     *
     * @param callable|null $resolve
     * @param callable|null $reject
     */
    public function fetchAsync(callable $resolve = null, callable $reject = null)
    {
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->uri,

            // You can set any number of default request options.
            'timeout'  => $this->getTimeout(),
        ]);

        $promise = $client->requestAsync($this->getMethod(), $this->getUri(), $this->getOptions());

        $promise->then(
            function ($result) use ($resolve, $reject) {
                $response = new Response(
                    $this->getMethod(),
                    $this->getUri(),
                    $result->getHeaders(),
                    $result->getBody(),
                    $result->getStatusCode()
                );

                if ($response->getStatusCode() == 200) { // handle 200 OK response
                    if (is_callable($resolve)) {
                        $resolve($response);
                    }
                } else {
                    if (is_callable($reject)) { // handle responses has error status
                        $reject($response);
                    }
                }
            }
        );
    }

    /**
     * An alias for fetchAsync
     *
     * @param callable|null $resolve
     * @param callable|null $reject
     * @throws \Exception
     */
    public function sendAsync(callable $resolve = null, callable $reject = null)
    {
        $this->fetch($resolve, $reject);
    }
}
