<?php

namespace Shetabit\Extractor\Tests\Feature;

use Shetabit\Extractor\Classes\Request;

/**
 * The cache middleware, over real connections to the stub server.
 */
final class CachingResponsesTest extends FeatureTestCase
{
    public function testASecondRequestIsAnsweredFromTheCache() : void
    {
        $first = new Request($this->server->url('/counter'))->cache(60)->fetch();
        $second = new Request($this->server->url('/counter'))->cache(60)->fetch();

        // The stub answers with a number that grows with every call, so an
        // answer that repeats itself can only come from the cache.
        $this->assertSame($first->getBody(), $second->getBody());

        $withoutCache = new Request($this->server->url('/counter'))->fetch();

        $this->assertNotSame($first->getBody(), $withoutCache->getBody());
    }

    public function testARequestOfAnotherUriHasACacheOfItsOwn() : void
    {
        $users = new Request($this->server->url('/?users'))->cache(60)->fetch();
        $posts = new Request($this->server->url('/?posts'))->cache(60)->fetch();

        $this->assertSame(['users' => ''], $this->echoed($users->getBody())['query']);
        $this->assertSame(['posts' => ''], $this->echoed($posts->getBody())['query']);
    }

    public function testARequestWithAnEventCallbackCanBeCached() : void
    {
        // The key of the cache entry used to be built by serializing the whole
        // request, which throws as soon as a callback is registered.
        $ran = 0;
        $onSuccess = function () use (&$ran): void {
            $ran++;
        };

        $first = new Request($this->server->url('/counter'))->cache(60)->fetch($onSuccess);
        $second = new Request($this->server->url('/counter'))->cache(60)->fetch($onSuccess);

        $this->assertSame($first->getBody(), $second->getBody());
        $this->assertSame(2, $ran);
    }
}
