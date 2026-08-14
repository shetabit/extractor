<?php

namespace Shetabit\Extractor\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Shetabit\Extractor\Classes\Bag;
use Shetabit\Extractor\Classes\Request;

final class RequestTest extends TestCase
{
    public function testItStartsOutAsAGetRequestOfTheRoot() : void
    {
        $request = new Request();

        $this->assertSame('/', $request->getUri());
        $this->assertSame('GET', $request->getMethod());
        $this->assertNull($request->getBody());
        $this->assertSame([], $request->getHeaders());
        $this->assertSame([], $request->getQueries());
        $this->assertSame(10, $request->getTimeout());
    }

    public function testItTakesTheUriTheMethodAndTheBodyOfItsConstructor() : void
    {
        $request = new Request('https://example.com/users', 'POST', 'a-body');

        $this->assertSame('https://example.com/users', $request->getUri());
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('a-body', $request->getBody());
    }

    public function testItTrimsTheUriItIsGiven() : void
    {
        $this->assertSame('https://example.com', new Request()->setUri('  https://example.com  ')->getUri());
    }

    public function testItReadsTheQueriesOfTheUri() : void
    {
        // The query string of the uri used to be dropped: the parser was asked
        // for the queries of an empty string, and answered with an empty list.
        $request = new Request('https://example.com/users?page=2&sort[]=name&sort[]=age');

        $this->assertSame(
            ['page' => '2', 'sort' => ['name', 'age']],
            $request->getQueries()
        );
        $this->assertSame('2', $request->getQuery('page'));
        $this->assertNull($request->getQuery('a-query-that-was-not-given'));
    }

    public function testTheQueriesOfTheUriAndTheOnesThatAreAddedLiveTogether() : void
    {
        $request = new Request('https://example.com/users?page=2');

        $request->addQuery('sort', 'name');

        $this->assertSame(['page' => '2', 'sort' => 'name'], $request->getQueries());
    }

    public function testTheUriCanBeParsed() : void
    {
        $request = new Request('https://example.com:8080/users?page=2#top');

        $parsed = $request->getParsedUri();

        $this->assertSame('https', $parsed['scheme']);
        $this->assertSame('example.com', $parsed['host']);
        $this->assertSame(8080, $parsed['port']);
        $this->assertSame('/users', $parsed['path']);
        $this->assertSame(['page' => '2'], $request->getParsedQueryString());
    }

    public function testHeadersCanBeAddedAndRead() : void
    {
        $request = new Request();

        $request->addHeader('Accept', 'application/json')->addHeader('X-Token', 'a-token');

        $this->assertSame('application/json', $request->getHeader('Accept'));
        $this->assertSame(['Accept' => 'application/json', 'X-Token' => 'a-token'], $request->getHeaders());

        // A header that was never added used to end in a TypeError.
        $this->assertNull($request->getHeader('X-Missing'));
    }

    public function testTheLastValueOfAHeaderWins() : void
    {
        $request = new Request()->addHeader('Accept', 'text/html')->addHeader('Accept', 'application/json');

        $this->assertSame('application/json', $request->getHeader('Accept'));
    }

    public function testFormParamsCanBeAddedAndRead() : void
    {
        $request = new Request()->addFormParam('name', 'Mahdi')->addFormParam('role', 'developer');

        $this->assertSame('Mahdi', $request->getFormParam('name'));
        $this->assertSame(['name' => 'Mahdi', 'role' => 'developer'], $request->getFormParams());
        $this->assertNull($request->getFormParam('a-param-that-was-not-given'));
    }

    public function testMultipartDataCanBeAddedAndRead() : void
    {
        $request = new Request()->addMultipartData('avatar', 'the-contents', ['Content-Type' => 'image/png']);

        $this->assertSame(
            [['name' => 'avatar', 'contents' => 'the-contents', 'headers' => ['Content-Type' => 'image/png']]],
            $request->getMultipartData()
        );
        $this->assertSame('avatar', $request->getMultipartData(0)['name']);
        $this->assertNull($request->getMultipartData(1));
    }

    public function testTheMisspelledMultipartMethodStillWorks() : void
    {
        $request = new Request()->addMultiparData('avatar', 'the-contents');

        $this->assertSame('avatar', $request->getMultipartData()[0]['name']);
    }

    public function testTheOptionsCarryEverythingTheRequestWasGiven() : void
    {
        $request = new Request('https://example.com/users?page=2', 'POST')
            ->setBody('a-body')
            ->addHeader('Accept', 'application/json')
            ->setVerify(false)
            ->setProxy('tcp://localhost:8125')
            ->allowRedirects(false);

        $options = $request->getOptions();

        $this->assertFalse($options['http_errors']);
        $this->assertFalse($options['allow_redirects']);
        $this->assertSame('a-body', $options['body']);
        $this->assertSame(['page' => '2'], $options['query']);
        $this->assertSame(['Accept' => 'application/json'], $options['headers']);
        $this->assertFalse($options['verify']);
        $this->assertSame('tcp://localhost:8125', $options['proxy']);
        $this->assertArrayNotHasKey('form_params', $options);
        $this->assertArrayNotHasKey('multipart', $options);
    }

    public function testTheOptionsCarryTheFormParams() : void
    {
        $options = new Request()->addFormParam('name', 'Mahdi')->getOptions();

        $this->assertSame(['name' => 'Mahdi'], $options['form_params']);
    }

    public function testTheOptionsCarryTheMultipartDataWhenThereAreNoFormParams() : void
    {
        $request = new Request()->addMultipartData('avatar', 'the-contents');

        $this->assertArrayHasKey('multipart', $request->getOptions());

        // The two can not be sent at the same time, the form params win.
        $request->addFormParam('name', 'Mahdi');

        $this->assertArrayHasKey('form_params', $request->getOptions());
        $this->assertArrayNotHasKey('multipart', $request->getOptions());
    }

    public function testAProxyIsOnlyAnOptionWhenThereIsOne() : void
    {
        $this->assertArrayNotHasKey('proxy', new Request()->getOptions());
    }

    public function testTheTimeoutCanBeChanged() : void
    {
        $this->assertSame(30, new Request()->setTimeout(30)->getTimeout());
    }

    public function testACallbackRunsWhenTheConditionIsTrue() : void
    {
        $request = new Request();

        $request
            ->when(true, fn (Request $request): Request => $request->addHeader('X-One', '1'))
            ->when(false, fn (Request $request): Request => $request->addHeader('X-Two', '2'))
            ->when(
                fn (Request $request): bool => $request->getMethod() === 'GET',
                fn (Request $request): Request => $request->addHeader('X-Three', '3')
            );

        $this->assertSame(['X-One' => '1', 'X-Three' => '3'], $request->getHeaders());
    }

    public function testACallbackRunsWhenTheConditionIsFalse() : void
    {
        $request = new Request();

        $request
            ->whenNot(false, fn (Request $request): Request => $request->addHeader('X-One', '1'))
            ->whenNot(true, fn (Request $request): Request => $request->addHeader('X-Two', '2'))
            ->whenNot(
                fn (Request $request): bool => $request->getMethod() === 'POST',
                fn (Request $request): Request => $request->addHeader('X-Three', '3')
            );

        $this->assertSame(['X-One' => '1', 'X-Three' => '3'], $request->getHeaders());
    }

    public function testItCreatesABagOfConcurrentRequests() : void
    {
        $this->assertInstanceOf(Bag::class, new Request()->createBag());
    }
}
