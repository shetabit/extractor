<?php

namespace Shetabit\Extractor\Classes;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;
use Shetabit\Extractor\Contracts\RequestInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;
use Shetabit\Extractor\Traits\Conditional;
use Shetabit\Extractor\Traits\HasMiddleware;
use Shetabit\Extractor\Traits\HasParsedUri;

class Request implements RequestInterface
{
    use HasParsedUri;
    use Conditional;
    use HasMiddleware;

    /**
     * Request's EndPoint
     */
    protected string $uri = '/';

    /**
     * Request's method
     */
    protected string $method = 'GET';

    /**
     * Request's custom headers
     *
     * @var array<string, string>
     */
    protected array $headers = [];

    /**
     * Used as request's body
     */
    protected mixed $body = null;

    /**
     * Used as request's multipart data
     *
     * @var array<int, array<string, mixed>>
     */
    protected array $multipartData = [];

    /**
     * Used to send request's params similar to forms
     *
     * @var array<string, mixed>
     */
    protected array $formParams = [];

    /**
     * Request's query
     *
     * @var array<string, mixed>
     */
    protected array $queries = [];

    /**
     * Deadline of each request (seconds)
     */
    protected float $timeout = 10.0; // 10 seconds

    /**
     * Follow redirects or not
     */
    protected bool $allowRedirects = true;

    /**
     * Describes the SSL certificate verification behavior of a request.
     */
    protected bool|string $verify = true;

    /**
     * String to specify an HTTP proxy, or an array to specify
     * different proxies for different protocols.
     *
     * @var array<string, string>|string|null
     */
    protected array|string|null $proxy = null;

    /**
     * Success event callback
     *
     * @var (callable(ResponseInterface, static): mixed)|null
     */
    protected $onSuccessCallback;

    /**
     * Error event callback
     *
     * @var (callable(ResponseInterface, static): mixed)|null
     */
    protected $onErrorCallback;

    /**
     * Request constructor.
     */
    public function __construct(string $uri = '/', string $method = 'GET', mixed $body = null)
    {
        $this->setUri($uri);
        $this->setMethod($method);
        $this->setBody($body);
    }

    /**
     * Set request's uri (endpoint)
     */
    public function setUri(string $url) : static
    {
        $this->uri = trim($url);

        $this->addUriQueries();

        return $this;
    }

    /**
     * Get request's endpoint
     */
    public function getUri() : string
    {
        return $this->uri;
    }

    /**
     * Set request's method (example: GET, POST, PUT, PATCH)
     */
    public function setMethod(string $method) : static
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get request's method
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * Add custom headers
     */
    public function addHeader(string $name, string $value) : static
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Get header by its name
     */
    public function getHeader(string $name) : string|null
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Retrieve all custom headers
     *
     * @return array<string, string>
     */
    public function getHeaders() : array
    {
        return $this->headers;
    }

    /**
     * Set request deadline (seconds)
     */
    public function setTimeout(int $timeout) : static
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Get request's deadline (seconds)
     */
    public function getTimeout() : int
    {
        return (int) $this->timeout;
    }

    /**
     * Follow redirects or not
     */
    public function allowRedirects(bool $allow = true) : static
    {
        $this->allowRedirects = $allow;

        return $this;
    }

    /**
     * Determine whether the redirects of a response are followed.
     */
    public function getAllowRedirects() : bool
    {
        return $this->allowRedirects;
    }

    /**
     * Set request's body
     */
    public function setBody(mixed $body) : static
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get request's body
     */
    public function getBody() : string|null
    {
        return $this->body === null ? null : (string) $this->body;
    }

    /**
     * Add form data
     */
    public function addFormParam(string $name, mixed $value) : static
    {
        $this->formParams[$name] = $value;

        return $this;
    }

    /**
     * Retrieve a single form param
     */
    public function getFormParam(string $name) : mixed
    {
        return $this->formParams[$name] ?? null;
    }

    /**
     * Retrieve all form params
     *
     * @return array<string, mixed>
     */
    public function getFormParams() : array
    {
        return $this->formParams;
    }

    /**
     * Add multipart data
     *
     * @param array<string, string> $headers
     */
    public function addMultipartData(string $name, mixed $value, array $headers = []) : static
    {
        $this->multipartData[] = [
            'name' => $name,
            'contents' => $value,
            'headers' => $headers,
        ];

        return $this;
    }

    /**
     * Add multipart data
     *
     * @param array<string, string> $headers
     *
     * @deprecated use `addMultipartData()`, this one carries a typo in its name
     */
    public function addMultiparData(string $name, mixed $value, array $headers = []) : static
    {
        return $this->addMultipartData($name, $value, $headers);
    }

    /**
     * Retrieve multipart data
     * if name is empty , all data will returned
     *
     * @return array<int, array<string, mixed>>|array<string, mixed>|null
     */
    public function getMultipartData(int|string|null $name = null) : array|null
    {
        return $name === null || $name === '' ? $this->multipartData : ($this->multipartData[$name] ?? null);
    }

    /**
     * Add query
     */
    public function addQuery(string $name, mixed $value) : static
    {
        $this->queries[$name] = $value;

        return $this;
    }

    /**
     * Retrieve a single query
     */
    public function getQuery(string $name) : mixed
    {
        return $this->queries[$name] ?? null;
    }

    /**
     * Get request's queries
     *
     * @return array<string, mixed>
     */
    public function getQueries() : array
    {
        return $this->queries;
    }

    /**
     * Set a proxy
     *
     * @param array<string, string>|string|null $proxy
     */
    public function setProxy(array|string|null $proxy) : static
    {
        $this->proxy = $proxy;

        return $this;
    }

    /**
     * Retrieve the current proxy
     *
     * @return array<string, string>|string|null
     */
    public function getProxy() : array|string|null
    {
        return $this->proxy;
    }

    /**
     * Describes the SSL certificate verification behavior of a request.
     */
    public function setVerify(bool|string $verify) : static
    {
        $this->verify = $verify;

        return $this;
    }

    /**
     * Retrieve SSL certificate verification behavior.
     */
    public function getVerify() : bool|string
    {
        return $this->verify;
    }

    /**
     * Generate options
     *
     * @return array<string, mixed>
     */
    public function getOptions() : array
    {
        $options = [
            'http_errors' => false,
            'allow_redirects' => $this->getAllowRedirects(),
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
        if ($this->getFormParams() !== []) {
            $options['form_params'] = $this->getFormParams();
        } elseif ($this->getMultipartData() !== []) {
            $options['multipart'] = $this->getMultipartData();
        }

        return $options;
    }

    /**
     * This event will be invoked when fetch complete successfully.
     *
     * @param callable(ResponseInterface, static): mixed $callback
     */
    public function onSuccess(callable $callback) : static
    {
        $this->onSuccessCallback = $callback;

        return $this;
    }

    /**
     * Trigger success event
     */
    public function success(ResponseInterface $response) : static
    {
        if (is_callable($this->onSuccessCallback)) {
            ($this->onSuccessCallback)($response, $this);
        }

        return $this;
    }

    /**
     * This event will be invoked when fetch fail.
     *
     * @param callable(ResponseInterface, static): mixed $callback
     */
    public function onError(callable $callback) : static
    {
        $this->onErrorCallback = $callback;

        return $this;
    }

    /**
     * Trigger error event
     */
    public function error(ResponseInterface $response) : static
    {
        if (is_callable($this->onErrorCallback)) {
            ($this->onErrorCallback)($response, $this);
        }

        return $this;
    }

    /**
     * Run and fetch data
     *
     * @param (callable(ResponseInterface, static): mixed)|null $resolve
     * @param (callable(ResponseInterface, static): mixed)|null $reject
     *
     * @throws GuzzleException
     */
    public function fetch(callable|null $resolve = null, callable|null $reject = null) : ResponseInterface
    {
        if ($resolve !== null) {
            $this->onSuccess($resolve);
        }

        if ($reject !== null) {
            $this->onError($reject);
        }

        $response = $this->invokeMiddlewares(
            $this,
            fn (RequestInterface $request): ResponseInterface => $this->sendRequest($request)
        );

        if ($response === null) {
            throw new RuntimeException('A middleware of this request answered with no response.');
        }

        if ($response->getStatusCode() === 200) { // handle 200 OK response
            $this->success($response);
        } else { // handle responses has error status
            $this->error($response);
        }

        return $response;
    }

    /**
     * An alias for fetch
     *
     * @param (callable(ResponseInterface, static): mixed)|null $resolve
     * @param (callable(ResponseInterface, static): mixed)|null $reject
     *
     * @throws GuzzleException
     */
    public function send(callable|null $resolve = null, callable|null $reject = null) : ResponseInterface
    {
        return $this->fetch($resolve, $reject);
    }

    /**
     * Create concurrent requests (factory method).
     */
    public function createBag() : Bag
    {
        return new Bag();
    }

    /**
     * The client the request is sent with.
     *
     * A test — or an application that has to hand its own handler stack over —
     * can replace it by extending this class.
     */
    protected function createClient() : Client
    {
        return new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->getUri(),

            // You can set any number of default request options.
            'timeout' => $this->getTimeout(),
        ]);
    }

    /**
     * Add uri's query string into query.
     */
    protected function addUriQueries() : static
    {
        foreach ($this->getParsedQueryString() as $name => $value) {
            $this->addQuery($name, $value);
        }

        return $this;
    }

    /**
     * Send the given request to its gateway.
     *
     * @throws GuzzleException
     */
    private function sendRequest(RequestInterface $request) : ResponseInterface
    {
        $result = $this->createClient()->request($request->getMethod(), $request->getUri(), $request->getOptions());

        return new Response(
            $request->getMethod(),
            $request->getUri(),
            $result->getHeaders(),
            (string) $result->getBody(),
            $result->getStatusCode()
        );
    }
}
