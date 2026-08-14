<?php

namespace Shetabit\Extractor\Tests\Unit;

use Shetabit\Extractor\Classes\Request;
use Shetabit\Extractor\Contracts\MiddlewareInterface;
use Shetabit\Extractor\Middlewares\CacheMiddleware;
use Shetabit\Extractor\Middlewares\Middleware;
use Shetabit\Extractor\Tests\Fixtures\CountingMiddleware;
use Shetabit\Extractor\Tests\TestCase;

final class HasMiddlewareTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        CountingMiddleware::$calls = [];
    }

    public function testAMiddlewareCanBeAdded() : void
    {
        $middleware = new Middleware();

        $request = new Request()->middleware($middleware);

        $this->assertSame([$middleware], $request->getMiddlewares());
    }

    public function testTheCacheShortcutAddsTheCacheMiddleware() : void
    {
        $middlewares = new Request()->cache(60)->getMiddlewares();

        $this->assertCount(1, $middlewares);
        $this->assertInstanceOf(CacheMiddleware::class, $middlewares[0]);
    }

    public function testGlobalMiddlewaresRunForEveryRequest() : void
    {
        Request::withGlobalMiddlewares([new CountingMiddleware('global'), CountingMiddleware::class]);

        $this->assertCount(2, new Request()->getMiddlewares());
        $this->assertCount(2, new Request()->getMiddlewares());
    }

    public function testTheGlobalMiddlewaresComeBeforeTheOnesOfTheRequest() : void
    {
        Request::withGlobalMiddlewares([new CountingMiddleware('global')]);

        $request = new Request()->middleware(new CountingMiddleware('local'));

        $this->assertSame(['global', 'local'], $this->namesOf($request->getMiddlewares()));
    }

    public function testAMiddlewareCanBeBanned() : void
    {
        Request::withGlobalMiddlewares([new CountingMiddleware('global')]);

        $request = new Request()
            ->middleware(new CountingMiddleware('local'))
            ->withoutMiddleware(new CountingMiddleware('global'));

        // The middleware to ban is recognized by what it is, so a second
        // instance of the very same middleware bans it as well.
        $this->assertSame(['local'], $this->namesOf($request->getMiddlewares()));
    }

    public function testAMiddlewareThatDoesNotStringifyCanBeBannedToo() : void
    {
        // The banned ones used to be told apart with `array_diff()`, which turns
        // every middleware into a string on the way.
        $middleware = new class () extends Middleware {
            public function __toString() : string
            {
                throw new \LogicException('A middleware should not have to be a string.');
            }
        };

        $request = new Request()->middleware($middleware)->withoutMiddleware($middleware);

        $this->assertSame([], $request->getMiddlewares());
    }

    /**
     * @param array<int, MiddlewareInterface> $middlewares
     *
     * @return array<int, string>
     */
    private function namesOf(array $middlewares) : array
    {
        $names = [];

        foreach ($middlewares as $middleware) {
            $names[] = $middleware instanceof CountingMiddleware ? $middleware->name : $middleware::class;
        }

        return $names;
    }

    public function testTheGlobalMiddlewaresCanBeThrownAway() : void
    {
        Request::withGlobalMiddlewares([new CountingMiddleware('global')]);

        Request::withoutGlobalMiddlewares();

        $this->assertSame([], new Request()->getMiddlewares());
    }
}
