<?php

namespace Shetabit\Extractor\Classes;

use Shetabit\Extractor\Contracts\RequestInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;
use Shetabit\Extractor\Traits\HasParsedUri;
use GuzzleHttp\Client;

class Request implements RequestInterface
{
    use HasParsedUri;

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
     * Follow redirects or not
     *
     * @var bool
     */
    protected $allowRedirects = true;

    /**
     * Describes the SSL certificate verification behavior of a request.
     *
     * @var boolean|string
     */
    protected $verify = true;

    /**
     * String to specify an HTTP proxy, or an array to specify
     * different proxies for different protocols.
     *
     * @var string|array
     */
    protected $proxy = null;

    /**
     * Success event callback
     *
     * @var callable|null
     */
    protected $onSuccessCallback = null;

    /**
     * Error event callback
     *
     * @var callable|null
     */
    protected $onErrorCallback = null;

    /**
     * Request constructor.
     *
     * @param string $uri
     * @param string $method
     * @param string|null $body
     */
    public function __construct(string $uri = '/', string $method = 'GET', string $body = null)
    {
        $this->setUri($uri);
        $this->setMethod($method);
        $this->setBody($body);
    }

    /**
     * Set request's uri (endpoint)
     *
     * @param string $url
     * @return $this|mixed
     */
    public function setUri(string $url)
    {
        $this->uri = trim($url);

        $this->addUriQueries($this->uri);

        return $this;
    }

    /**
     * Add uri's query string into query.
     *
     * @return $this|mixed
    */
    protected function addUriQueries($uri)
    {
        $queries = $this->getParsedQueryString($uri);

        if (is_array($queries)) {
            foreach ($queries as $name => $value) {
                $this->addQuery($name, $value);
            }
        }

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
     *
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
     *
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
    public function getHeaders() : array
    {
        return $this->headers;
    }

    /**
     * Set request deadline (seconds)
     *
     * @param int $timeout
     *
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
        return (int) $this->timeout;
    }

    /**
     * Follow redirects or not
     *
     * @param bool $allow
     *
     * @return $this|mixed
     */
    public function allowRedirects(bool $allow = true)
    {
        $this->allowRedirects = $allow;

        return $this;
    }

    /**
     * Set request's body
     *
     * @param $body
     *
     * @return $this|mixed
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get request's body
     *
     * @return string|null
     */
    public function getBody() : ?string
    {
        return $this->body;
    }

    /**
     * Add form data
     *
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function addFormParam($name, $value)
    {
        $this->formParams = array_merge($this->formParams, [$name => $value]);

        return $this;
    }

    /**
     * Retrieve multipart data
     * if name is empty , all data will returned
     *
     * @param $name
     *
     * @return mixed|null
     */
    public function getFormParam($name)
    {
        return $this->formParams[$name] ?? null;
    }

    /**
     * Retrieve all form params
     *
     * @return array
     */
    public function getFormParams()
    {
        return $this->formParams;
    }

    /**
     * Add form data
     *
     * @param $name
     * @param $value
     * @param array $headers
     *
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
     * Retrieve multipart data
     * if name is empty , all data will returned
     *
     * @param null $name
     *
     * @return array|mixed|null
     */
    public function getMultipartData($name = null)
    {
        return empty($name) ? $this->multipartData : ($this->multipartData[$name] ?? null);
    }

    /**
     * Add query
     *
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function addQuery($name, $value)
    {
        $this->queries = array_merge($this->queries, [$name => $value]);

        return $this;
    }

    /**
     * Retrieve multipart data
     * if name is empty , all data will returned
     *
     * @param $name
     *
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
     * Set a proxy
     *
     * @param mixed $proxy
     *
     * @return $this
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;

        return $this;
    }

    /**
    * Retrieve the current proxy
    *
    * @return mixed|null
    */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * Describes the SSL certificate verification behavior of a request.
     *
     * @param mixed $verify
     *
     * @return $this
     */
    public function setVerify($verify)
    {
        $this->verify = $verify;

        return $this;
    }

    /**
     * Retrieve SSL certificate verification behavior.
     *
     * @return bool|string
     */
    public function getVerify()
    {
        return $this->verify;
    }

    /**
     * Generate options
     *
     * @return array
     */
    public function getOptions()
    {
        $options = [
            'http_errors' => false,
            'body' => $this->getBody(),
            'query' => $this->getQueries(),
            'headers' => $this->getHeaders(),
            'verify' => $this->getVerify(),
        ];

        if ($proxy = $this->getProxy()) {
            $options['proxy'] = $proxy;
        }

        /*
         * we can't use formParams and MultipartData at the same time.
         * this part selects one of them.
         */
        if (!empty($this->getFormParams())) {
            $options['form_params'] = $this->getFormParams();
        } elseif (!empty($this->getMultipartData())) {
            $options['multipart'] = $this->getMultipartData();
        }

        return $options;
    }

    /**
     * This event will be invoked when fetch complete successfully.
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function onSuccess(callable $callback)
    {
        $this->onSuccessCallback = $callback;

        return $this;
    }

    /**
     * Trigger success event
     *
     * @param ResponseInterface $response
     *
     * @return $this
     */
    public function success(ResponseInterface $response)
    {
        if (is_callable($this->onSuccessCallback)) {
            $success = $this->onSuccessCallback;
            $success($response, $request);
        }

        return $this;
    }

    /**
     * This event will be invoked when fetch fail.
     *
     * @param callable $callback
     * @return $this
     */
    public function onError(callable $callback)
    {
        $this->onErrorCallback = $callback;

        return $this;
    }

    /**
     * Trigger error event
     *
     * @param ResponseInterface $response
     *
     * @return $this
     */
    public function error(ResponseInterface $response)
    {
        if (is_callable($this->onErrorCallback)) {
            $error = $this->onErrorCallback;
            $error($response, $this);
        }

        return $this;
    }

    /**
     * Run and fetch data
     *
     * @param callable|null $resolve
     * @param callable|null $reject
     *
     * @return ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function fetch(callable $resolve = null, callable $reject = null) : ResponseInterface
    {
        if (is_callable($resolve)) {
            $this->onSuccess($resolve);
        }

        if (is_callable($reject)) {
            $this->onError($reject);
        }

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
            if (is_callable($this->onSuccess)) {
                $this->success($response);
            }
        } else {
            if (is_callable($this->onError)) { // handle responses has error status
                $this->error($response);
            }
        }

        return $response;
    }

    /**
     * An alias for fetch
     *
     * @param callable|null $resolve
     * @param callable|null $reject
     *
     * @return ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(callable $resolve = null, callable $reject = null)
    {
        return $this->fetch($resolve, $reject);
    }

    /**
     * Create concurrent requests.
     *
     * @return Bag
     */
    public function createBag()
    {
        return new Bag();
    }
}
