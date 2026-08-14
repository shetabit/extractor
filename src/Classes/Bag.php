<?php

namespace Shetabit\Extractor\Classes;

use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Shetabit\Extractor\Classes\Request as BaseRequest;
use Shetabit\Extractor\Contracts\RequestInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;
use Throwable;

class Bag
{
    /**
     * List of requests
     *
     * @var array<int, array{request: RequestInterface, response: ResponseInterface|null}>
     */
    protected array $requests = [];

    /**
     * Number of maximum concurrent requests
     */
    protected int|null $concurrency = null;

    /**
     * Add a new request into the bag reserved requests.
     *
     * @param RequestInterface|(callable(BaseRequest): mixed)|null $request
     */
    public function addRequest(RequestInterface|callable|null $request = null) : static
    {
        $this->requests[] = [
            'request' => $request instanceof RequestInterface ? $request : $this->createAndPrepareARequest($request),
            'response' => null,
        ];

        return $this;
    }

    /**
     * Retrieve all requests.
     *
     * @return array<int, array{request: RequestInterface, response: ResponseInterface|null}>
     */
    public function getRequests() : array
    {
        return $this->requests;
    }

    /**
     * Set max concurrency.
     * set it to 0 or negate values if you want max concurrency
     */
    public function setConcurrency(int $concurrency) : static
    {
        $this->concurrency = $concurrency;

        return $this;
    }

    /**
     * Retrieve current concurrency.
     */
    public function getConcurrency() : int
    {
        return (int) $this->concurrency;
    }

    /**
     * Execute bag requests.
     *
     * @param (callable(ResponseInterface, RequestInterface): mixed)|null $resolve
     * @param (callable(ResponseInterface, RequestInterface): mixed)|null $reject
     *
     * @return array<int, ResponseInterface|null>
     */
    public function fetch(callable|null $resolve = null, callable|null $reject = null) : array
    {
        $client = $this->createClient();

        $pool = new Pool(
            $client,
            $this->prepareRequestPromises($client),
            $this->preparePoolConfigs($resolve, $reject)
        );

        // Initiate the transfers and create a promise, then force the pool of
        // requests to complete.
        $pool->promise()->wait();

        return array_column($this->getRequests(), 'response');
    }

    /**
     * The client the requests of this bag are sent with.
     *
     * A test — or an application that has to hand its own handler stack over —
     * can replace it by extending this class.
     */
    protected function createClient() : Client
    {
        return new Client();
    }

    /**
     * Prepare requests promise.
     *
     * @return Generator<int, callable(): mixed>
     */
    protected function prepareRequestPromises(Client $client) : Generator
    {
        foreach ($this->requests as $data) {
            $request = $data['request'];

            yield fn (): \GuzzleHttp\Promise\PromiseInterface => $client->requestAsync(
                $request->getMethod(),
                $request->getUri(),
                $request->getOptions()
            );
        }
    }

    /**
     * Create new request and set it configs by running given callback.
     *
     * @param (callable(BaseRequest): mixed)|null $callback
     */
    protected function createAndPrepareARequest(callable|null $callback = null) : BaseRequest
    {
        $request = new BaseRequest();

        if ($callback !== null) {
            $callback($request);
        }

        return $request;
    }

    /**
     * Prepare configs
     *
     * @param (callable(ResponseInterface, RequestInterface): mixed)|null $resolve
     * @param (callable(ResponseInterface, RequestInterface): mixed)|null $reject
     *
     * @return array<string, mixed>
     */
    protected function preparePoolConfigs(callable|null $resolve = null, callable|null $reject = null) : array
    {
        $concurrency = $this->getConcurrency() > 0 ? $this->getConcurrency() : count($this->requests);

        return [
            'concurrency' => max($concurrency, 1),
            'fulfilled' => function (PsrResponseInterface $result, int $index) use ($resolve, $reject): void {
                // this is delivered each successful response
                $request = $this->requests[$index]['request'];

                $response = $this->rememberResponse($index, new Response(
                    $request->getMethod(),
                    $request->getUri(),
                    $result->getHeaders(),
                    (string) $result->getBody(),
                    $result->getStatusCode()
                ));

                if ($response->getStatusCode() === 200) { // handle 200 OK response
                    $request->success($response);

                    if ($resolve !== null) {
                        $resolve($response, $request);
                    }

                    return;
                }

                // handle responses has error status
                $request->error($response);

                if ($reject !== null) {
                    $reject($response, $request);
                }
            },
            'rejected' => function (Throwable $reason, int $index) use ($reject): void {
                // this is delivered each failed request
                $request = $this->requests[$index]['request'];

                $response = $this->rememberResponse($index, new Response(
                    $request->getMethod(),
                    $request->getUri(),
                    [],
                    $reason->getMessage(),
                    0
                ));

                $request->error($response);

                if ($reject !== null) {
                    $reject($response, $request);
                }
            },
        ];
    }

    /**
     * Keep the response of the request at the given index.
     */
    private function rememberResponse(int $index, ResponseInterface $response) : ResponseInterface
    {
        $this->requests[$index]['response'] = $response;

        return $response;
    }
}
