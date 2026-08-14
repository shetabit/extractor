<?php

namespace Shetabit\Extractor\Tests\Feature;

use RuntimeException;
use Shetabit\Extractor\Classes\Request;
use Shetabit\Extractor\Contracts\ResponseInterface;
use Shetabit\Extractor\Tests\Fixtures\CountingMiddleware;
use Shetabit\Extractor\Tests\Fixtures\EchoClient;
use Shetabit\Extractor\Tests\Fixtures\ShortCircuitMiddleware;
use Shetabit\Extractor\Tests\Fixtures\SilentMiddleware;

/**
 * Requests that are sent over a real connection to the stub server.
 */
final class SendingRequestsTest extends FeatureTestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        CountingMiddleware::$calls = [];
    }

    public function testItSendsAGetRequestAndReadsTheResponse() : void
    {
        $response = new Request($this->server->url('/'))->fetch();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('GET', $response->getMethod());
        $this->assertSame(['extractor'], $response->getHeader('X-Stub-Server'));

        $echoed = $this->echoed($response->getBody());

        $this->assertSame('GET', $echoed['method']);
        $this->assertSame('/', $echoed['path']);
    }

    public function testItSendsTheHeadersAndTheQueriesOfTheRequest() : void
    {
        $response = new Request($this->server->url('/?page=2'))
            ->addHeader('X-Token', 'a-token')
            ->addQuery('sort', 'name')
            ->fetch();

        $echoed = $this->echoed($response->getBody());

        // The query of the uri used to be dropped on the way.
        $this->assertSame(['page' => '2', 'sort' => 'name'], $echoed['query']);
        $this->assertSame('a-token', $echoed['headers']['x-token'] ?? null);
    }

    public function testItSendsABody() : void
    {
        $response = new Request($this->server->url('/'), 'POST', '{"name":"Mahdi"}')
            ->addHeader('Content-Type', 'application/json')
            ->fetch();

        $echoed = $this->echoed($response->getBody());

        $this->assertSame('POST', $echoed['method']);
        $this->assertSame('{"name":"Mahdi"}', $echoed['body']);
    }

    public function testItSendsFormParams() : void
    {
        $response = new Request($this->server->url('/'), 'POST')
            ->addFormParam('name', 'Mahdi')
            ->addFormParam('role', 'developer')
            ->fetch();

        $echoed = $this->echoed($response->getBody());

        $this->assertSame(['name' => 'Mahdi', 'role' => 'developer'], $echoed['form']);
    }

    public function testItSendsMultipartData() : void
    {
        $response = new Request($this->server->url('/'), 'POST')
            ->addMultipartData('avatar', 'the-contents')
            ->fetch();

        $echoed = $this->echoed($response->getBody());

        $this->assertStringContainsString('multipart/form-data', $echoed['headers']['content-type'] ?? '');

        // PHP reads the parts of a multipart body into its form data.
        $this->assertSame(['avatar' => 'the-contents'], $echoed['form']);
    }

    public function testTheSuccessCallbackRunsForAnOkResponse() : void
    {
        $received = null;

        $request = new Request($this->server->url('/'));
        $response = $request->fetch(function (ResponseInterface $response, Request $sent) use (&$received): void {
            $received = [$response, $sent];
        });

        $this->assertNotNull($received);
        $this->assertSame($response, $received[0]);
        $this->assertSame($request, $received[1]);
    }

    public function testTheErrorCallbackRunsForAResponseThatIsNotOk() : void
    {
        $status = null;

        new Request($this->server->url('/status/503'))->fetch(
            function (): never {
                $this->fail('The success callback ran for a 503.');
            },
            function (ResponseInterface $response) use (&$status): void {
                $status = $response->getStatusCode();
            }
        );

        $this->assertSame(503, $status);
    }

    public function testTheCallbacksCanBeRegisteredBeforeTheRequestIsSent() : void
    {
        $ran = [];

        new Request($this->server->url('/'))
            ->onSuccess(function () use (&$ran): void {
                $ran[] = 'success';
            })
            ->onError(function () use (&$ran): void {
                $ran[] = 'error';
            })
            ->fetch();

        $this->assertSame(['success'], $ran);
    }

    public function testSendIsAnAliasOfFetch() : void
    {
        $this->assertSame(200, new Request($this->server->url('/'))->send()->getStatusCode());
    }

    public function testTheMiddlewaresRunInTheOrderTheyWereAdded() : void
    {
        $response = new Request($this->server->url('/'))
            ->middleware(new CountingMiddleware('first'))
            ->middleware(new CountingMiddleware('second'))
            ->fetch();

        $this->assertSame(['first', 'second'], CountingMiddleware::$calls);

        // Every middleware sees the request before it is sent.
        $echoed = $this->echoed($response->getBody());

        $this->assertSame('first,second', $echoed['headers']['x-middlewares'] ?? null);
    }

    public function testAMiddlewareCanAnswerOnItsOwn() : void
    {
        $response = new Request($this->server->url('/'))
            ->middleware(new ShortCircuitMiddleware('from-the-middleware'))
            ->middleware(new CountingMiddleware('never-reached'))
            ->fetch();

        $this->assertSame('from-the-middleware', $response->getBody());
        $this->assertSame([], CountingMiddleware::$calls);
    }

    public function testARequestFailsLoudlyWhenAMiddlewareAnswersWithNothing() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('A middleware of this request answered with no response.');

        new Request($this->server->url('/'))->middleware(new SilentMiddleware())->fetch();
    }

    public function testAGlobalMiddlewareRunsForEveryRequest() : void
    {
        Request::withGlobalMiddlewares([new CountingMiddleware('global')]);

        new Request($this->server->url('/'))->fetch();
        new Request($this->server->url('/'))->fetch();

        $this->assertSame(['global', 'global'], CountingMiddleware::$calls);
    }

    public function testAClientSendsItsRequest() : void
    {
        $response = new EchoClient($this->server->url('/'))->run();

        $this->assertSame(200, $response?->getStatusCode());
    }
}
