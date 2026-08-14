<?php

namespace Shetabit\Extractor\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Shetabit\Extractor\Classes\Response;

final class ResponseTest extends TestCase
{
    public function testItCarriesWhatItWasBuiltWith() : void
    {
        $response = $this->response();

        $this->assertSame('GET', $response->getMethod());
        $this->assertSame('https://example.com/users?page=2', $response->getUri());
        $this->assertSame(['Content-Type' => ['application/json']], $response->getHeaders());
        $this->assertSame('{"data":[]}', $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testAHeaderCanBeReadByItsName() : void
    {
        $response = $this->response();

        $this->assertSame(['application/json'], $response->getHeader('Content-Type'));
        $this->assertNull($response->getHeader('X-Missing'));
    }

    public function testTheUriOfTheResponseCanBeParsed() : void
    {
        $response = $this->response();

        $this->assertSame('example.com', $response->getParsedUri()['host']);
        $this->assertSame(['page' => '2'], $response->getParsedQueryString());
    }

    private function response() : Response
    {
        return new Response(
            'GET',
            'https://example.com/users?page=2',
            ['Content-Type' => ['application/json']],
            '{"data":[]}',
            200
        );
    }
}
