<?php

namespace Shetabit\Extractor\Tests\Unit;

use Shetabit\Extractor\Classes\Request;
use Shetabit\Extractor\Classes\Response;
use Shetabit\Extractor\Contracts\RequestInterface;
use Shetabit\Extractor\Contracts\ResponseInterface;
use Shetabit\Extractor\Middlewares\CacheMiddleware;
use Shetabit\Extractor\Tests\TestCase;

final class CacheMiddlewareTest extends TestCase
{
    public function testItStoresTheResponseOfARequest() : void
    {
        $middleware = new CacheMiddleware(60);
        $request = new Request('https://example.com/users');

        $response = $middleware->handle($request, fn (): ResponseInterface => $this->response('from-the-network'));

        $this->assertSame('from-the-network', $response?->getBody());

        // A second run of the same request is answered from the cache, the
        // callback that would send it is never reached.
        $second = $middleware->handle($request, function (): never {
            $this->fail('The request was sent although its response was cached.');
        });

        $this->assertSame('from-the-network', $second?->getBody());
    }

    public function testEveryRequestHasItsOwnCacheEntry() : void
    {
        $middleware = new CacheMiddleware(60);

        $middleware->handle(
            new Request('https://example.com/users'),
            fn (): ResponseInterface => $this->response('users')
        );

        $response = $middleware->handle(
            new Request('https://example.com/posts'),
            fn (): ResponseInterface => $this->response('posts')
        );

        $this->assertSame('posts', $response?->getBody());
    }

    public function testItStoresNothingWhenTheChainAnswersWithNothing() : void
    {
        $middleware = new CacheMiddleware(60);
        $request = new Request('https://example.com/users');

        $sent = 0;
        $answerWithNothing = function () use (&$sent): null {
            $sent++;

            return null;
        };

        $this->assertNull($middleware->handle($request, $answerWithNothing));
        $this->assertNull($middleware->handle($request, $answerWithNothing));

        // Nothing was cached, so the second run reached the chain again.
        $this->assertSame(2, $sent);
    }

    private function response(string $body) : ResponseInterface
    {
        return new Response('GET', 'https://example.com/users', [], $body, 200);
    }
}
