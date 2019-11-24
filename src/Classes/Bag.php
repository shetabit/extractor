<?php

namespace Shetabit\Extractor\Classes;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Shetabit\Extractor\Contracts\RequestInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;

class Bag
{
    /**
     * List of requests
     *
     * @var array
     */
    protected $requests;

    /**
     * Number of maximum concurrent requests
     *
     * @var int|null
     */
    protected $concurrency = null;

    /**
     * Add a new request into the bag reserved requests.
     *
     * @param null $request
     * @param callable|null $resolve
     * @param callable|null $reject
     *
     * @return $this
     */
    public function addRequest($request = null, callable $resolve = null, callable $reject = null)
    {
        $requestInstance = $request;

        if (! ($request instanceof RequestInterface)) {
            $requestInstance = $this->createAndPrepareARequest($request);
        }

        $this->requests[] = [
            'request' => $requestInstance,
            'resolve' => $resolve,
            'reject' => $reject,
        ];

        return $this;
    }

    /**
     * Retrieve all requests.
     *
     * @return mixed
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * Set max concurrency.
     * set it to 0 or negate values if you want max concurrency
     *
     * @param int $concurrency
     * @return $this
     */
    public function setConcurrency(int $concurrency)
    {
        $this->concurrency = $concurrency;

        return $this;
    }

    /**
     * Retrieve current concurrency.
     *
     * @return int
     */
    public function getConcurrency()
    {
        return (int) $this->concurrency;
    }

    /**
     * Execute bag requests.
     *
     * @param callable $fulfilled
     * @param callable $rejected
     */
    public function execute(callable $fulfilled, callable $rejected)
    {
        $concurrency = $this->getConcurrency() > 0 ? $this->getConcurrency() : count($this->requests);

        $client = new Client();

        $pool = new Pool($client, $this->prepareRequestPromises($client), [
            'concurrency' => $concurrency,
            'fulfilled' => function ($response, $index) use ($fulfilled) {
                // this is delivered each successful response
                if (is_callable($fulfilled)) {
                    $fulfilled();
                }
            },
            'rejected' => function ($reason, $index) use ($rejected) {
                // this is delivered each failed request
                if (is_callable($rejected)) {
                    $rejected();
                }
            },
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();
    }

    /**
     * Prepare requests promise.
     *
     * @param $client
     *
     * @return \Generator
     */
    protected function prepareRequestPromises($client)
    {
        foreach ($this->requests as $data) {
            $request = $data['request'];
            $resolve = $data['resolve'];
            $reject = $data['reject'];

            yield function () use ($client, $request, $resolve, $reject) {
                $promise = $client->requestAsync(
                    $request->getMethod(),
                    $request->getUri(),
                    $request->getOptions()
                );

                $promise->then(
                    function (ResponseInterface $response) use ($resolve) {
                        if (is_callable($resolve)) {
                            $resolve('', $this);
                        }
                    },
                    function (RequestException $exception) use ($reject) {
                        if (is_callable($reject)) {
                            $reject('', $this);
                        }
                    }
                );

                return $promise;
            };
        }
    }

    /**
     * Create new request and set it configs by running given callback.
     *
     * @param null $callback
     *
     * @return Request
     */
    protected function createAndPrepareARequest($callback = null)
    {
        $request = new Request();

        if (is_callable($callback)) {
            $callback($request);
        }

        return $request;
    }
}