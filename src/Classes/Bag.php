<?php

namespace Shetabit\Extractor\Classes;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Shetabit\Extractor\Classes\Request as BaseRequest;
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
     *
     * @return $this
     */
    public function addRequest($request = null)
    {
        $requestInstance = $request;

        if (! ($request instanceof RequestInterface)) {
            $requestInstance = $this->createAndPrepareARequest($request);
        }

        $this->requests[] = [
            'request' => $requestInstance,
            'response' => null,
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
    public function execute(callable $fulfilled = null, callable $rejected = null) : array
    {
        $client = new Client;
        $pool = new Pool($client, $this->prepareRequestPromises($client), $this->preparePoolConfigs($fulfilled, $rejected));

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();

        return array_column($this->getRequests(), 'response');
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
        foreach ($this->requests as $index => $data) {
            $request = $data['request'];

            yield function () use ($client, $request) {
                $promise = $client->requestAsync(
                    $request->getMethod(),
                    $request->getUri(),
                    $request->getOptions()
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
        $request = new BaseRequest();

        if (is_callable($callback)) {
            $callback($request);
        }

        return $request;
    }

    /**
     * Prepare configs
     *
     * @param callable|null $fulfilled
     * @param callable|null $rejected
     *
     * @return array
     */
    protected function preparePoolConfigs(callable $fulfilled = null, callable $rejected = null)
    {
        $concurrency = $this->getConcurrency() > 0 ? $this->getConcurrency() : count($this->requests);

        return [
            'concurrency' => $concurrency,
            'fulfilled' => function ($result, $index) use ($fulfilled) {
                // this is delivered each successful response
                $request = $this->getRequests()[$index]['request'];

                $response = new Response(
                    $request->getMethod(),
                    $request->getUri(),
                    $result->getHeaders(),
                    $result->getBody(),
                    $result->getStatusCode()
                );

                $this->requests[$index]['response'] = $response;

                if ($response->getStatusCode() == 200) { // handle 200 OK response
                    $request->success($response);
                    if (is_callable($fulfilled)) {
                        $fulfilled($response, $request);
                    }
                } else { // handle responses has error status
                    $request->error($response);
                    if (is_callable($rejected)) {
                        $rejected($response, $request);
                    }
                }
            },
            'rejected' => function ($result, $index) use ($rejected) {
                // this is delivered each failed request
                $request = $this->getRequests()[$index]['request'];
                $resolve = $this->getRequests()[$index]['resolve'];
                $reject = $this->getRequests()[$index]['reject'];
                $response = new Response(
                    $request->getMethod(),
                    $request->getUri(),
                    [],
                    '',
                    0
                );

                $this->requests[$index]['response'] = $response;

                if (is_callable($reject)) {
                    $reject($response, $request);
                }
                if (is_callable($rejected)) {
                    $rejected($response, $request);
                }
            },
        ];
    }
}
