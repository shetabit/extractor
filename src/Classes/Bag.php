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
    protected $requests;

    protected $concurrency = null;

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

    public function getRequests()
    {
        return $this->requests;
    }

    public function setConcurrency(int $concurrency)
    {
        $this->concurrency = $concurrency;

        return $this;
    }

    public function getConcurrency()
    {
        return (int) $this->concurrency;
    }

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