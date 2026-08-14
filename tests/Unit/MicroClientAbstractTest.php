<?php

namespace Shetabit\Extractor\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Shetabit\Extractor\Classes\Request;
use Shetabit\Extractor\Contracts\MicroClientInterface;
use Shetabit\Extractor\Tests\Fixtures\EchoClient;

final class MicroClientAbstractTest extends TestCase
{
    public function testAClientIsBuiltWithARequestOfItsOwn() : void
    {
        $client = new EchoClient();

        $this->assertInstanceOf(MicroClientInterface::class, $client);
        $this->assertInstanceOf(Request::class, $client->createBag()->addRequest()->getRequests()[0]['request']);
    }

    public function testTheMethodsOfTheRequestCanBeReachedThroughTheClient() : void
    {
        $client = new EchoClient();

        $client->setUri('https://example.com/users')->addHeader('Accept', 'application/json');

        $this->assertSame('https://example.com/users', $client->getUri());
        $this->assertSame(['Accept' => 'application/json'], $client->getHeaders());
    }
}
