<?php

namespace Shetabit\Extractor\Abstracts;

use Shetabit\Extractor\Classes\Request;
use Shetabit\Extractor\Contracts\MicroClientInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;

/**
 * The methods of the request are reachable through the client itself.
 *
 * @method Request setUri(string $url)
 * @method string getUri()
 * @method Request setMethod(string $method)
 * @method string getMethod()
 * @method Request addHeader(string $name, string $value)
 * @method string|null getHeader(string $name)
 * @method array<string, string> getHeaders()
 * @method Request setTimeout(int $timeout)
 * @method int getTimeout()
 * @method Request allowRedirects(bool $allow = true)
 * @method Request setBody(mixed $body)
 * @method string|null getBody()
 * @method Request addFormParam(string $name, mixed $value)
 * @method Request addMultipartData(string $name, mixed $value, array<string, string> $headers = [])
 * @method Request addQuery(string $name, mixed $value)
 * @method array<string, mixed> getQueries()
 * @method Request setProxy(array<string, string>|string|null $proxy)
 * @method Request setVerify(bool|string $verify)
 * @method array<string, mixed> getOptions()
 * @method Request middleware(\Shetabit\Extractor\Contracts\MiddlewareInterface $middleware)
 * @method Request withoutMiddleware(\Shetabit\Extractor\Contracts\MiddlewareInterface $middleware)
 * @method Request cache(int $ttl = 10)
 * @method Request when(mixed $condition, callable $callback)
 * @method Request whenNot(mixed $condition, callable $callback)
 * @method Request onSuccess(callable $callback)
 * @method Request onError(callable $callback)
 * @method ResponseInterface fetch(callable|null $resolve = null, callable|null $reject = null)
 * @method ResponseInterface send(callable|null $resolve = null, callable|null $reject = null)
 * @method \Shetabit\Extractor\Classes\Bag createBag()
 */
abstract class MicroClientAbstract implements MicroClientInterface
{
    /**
     * Request handler
     * Can be used to send request between remote micro services.
     */
    protected Request $request;

    /**
     * MicroClientAbstract constructor.
     */
    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * Run client
     */
    abstract public function run() : ResponseInterface|null;

    /**
     * Access to request methods directly
     *
     * @param array<int, mixed> $params
     */
    public function __call(string $name, array $params) : mixed
    {
        return $this->request->{$name}(...$params);
    }
}
