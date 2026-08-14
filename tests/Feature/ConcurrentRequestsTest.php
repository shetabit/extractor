<?php

namespace Shetabit\Extractor\Tests\Feature;

use Shetabit\Extractor\Classes\Bag;
use Shetabit\Extractor\Classes\Request;
use Shetabit\Extractor\Contracts\ResponseInterface;

/**
 * Bags of requests that are sent over real connections to the stub server.
 */
final class ConcurrentRequestsTest extends FeatureTestCase
{
    public function testItSendsEveryRequestOfTheBag() : void
    {
        $bag = new Bag()
            ->addRequest(new Request($this->server->url('/?first')))
            ->addRequest(new Request($this->server->url('/?second')));

        $responses = $bag->fetch();

        $this->assertCount(2, $responses);
        $this->assertSame(200, $responses[0]?->getStatusCode());
        $this->assertSame(200, $responses[1]?->getStatusCode());
        $this->assertSame(['first' => ''], $this->echoed($responses[0]->getBody())['query']);
        $this->assertSame(['second' => ''], $this->echoed($responses[1]->getBody())['query']);
    }

    public function testARequestOfTheBagCanBeBuiltByACallback() : void
    {
        $responses = new Bag()
            ->addRequest(function (Request $request): void {
                $request->setUri($this->server->url('/'))->setMethod('POST')->addFormParam('name', 'Mahdi');
            })
            ->fetch();

        $echoed = $this->echoed($responses[0]->getBody());

        $this->assertSame('POST', $echoed['method']);
        $this->assertSame(['name' => 'Mahdi'], $echoed['form']);
    }

    public function testTheCallbacksRunForEveryRequest() : void
    {
        $resolved = [];
        $rejected = [];

        new Bag()
            ->addRequest(new Request($this->server->url('/')))
            ->addRequest(new Request($this->server->url('/status/500')))
            ->fetch(
                function (ResponseInterface $response) use (&$resolved): void {
                    $resolved[] = $response->getStatusCode();
                },
                function (ResponseInterface $response) use (&$rejected): void {
                    $rejected[] = $response->getStatusCode();
                }
            );

        $this->assertSame([200], $resolved);
        $this->assertSame([500], $rejected);
    }

    public function testTheEventsOfEveryRequestOfTheBagAreTriggered() : void
    {
        $events = [];

        $ok = new Request($this->server->url('/'))->onSuccess(function () use (&$events): void {
            $events[] = 'success';
        });

        $failing = new Request($this->server->url('/status/404'))->onError(function () use (&$events): void {
            $events[] = 'error';
        });

        new Bag()->addRequest($ok)->addRequest($failing)->fetch();

        $this->assertSame(['success', 'error'], $events);
    }

    public function testARequestThatCanNotBeSentIsRejected() : void
    {
        $rejected = [];

        // Nothing listens on that port, so the connection is refused.
        $responses = new Bag()
            ->addRequest(new Request('http://127.0.0.1:'.$this->server->unusedPort().'/'))
            ->fetch(null, function (ResponseInterface $response) use (&$rejected): void {
                $rejected[] = $response->getStatusCode();
            });

        // The reject callback used to be overwritten with the value of a key
        // that is not there, so it never ran for a failed request.
        $this->assertSame([0], $rejected);
        $this->assertSame(0, $responses[0]->getStatusCode());

        // The response of a request that never reached its server carries the
        // reason it did not.
        $this->assertNotSame('', $responses[0]->getBody());
    }

    public function testTheConcurrencyCanBeChanged() : void
    {
        $bag = new Bag()->setConcurrency(5);

        $this->assertSame(5, $bag->getConcurrency());
    }

    public function testAnEmptyBagFetchesNothing() : void
    {
        $this->assertSame([], new Bag()->fetch());
    }
}
