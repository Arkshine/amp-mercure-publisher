<?php

require __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Symfony\Component\Mercure\Jwt\StaticJwtProvider;
use Symfony\Component\Mercure\Update;
use Amp\Success;
use Islambey\Amp\Mercure\Publisher;
use function Amp\Promise\wait;
use Amp\Artax\Request;

class PublisherTest extends TestCase
{
    public function testPublish()
    {
        \DG\BypassFinals::enable();
        $jwt = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyJmb28iLCJiYXIiXSwicHVibGlzaCI6WyJmb28iXX19.LRLvirgONK13JgacQ_VbcjySbVhkSmHy3IznH3tA9PM';
        $hubUri = 'https://example.com/hub';
        $jwtProvider = new StaticJwtProvider($jwt);
        $update = new Update('https://example.com', 'data');
        $message = new \Amp\ByteStream\Message(new \Amp\ByteStream\InMemoryStream('id'));

        $httpClient = $this->getMockBuilder(\Amp\Artax\DefaultClient::class)
            ->getMock();

        $response = $this->getMockBuilder(\Amp\Artax\Response::class)
            ->getMock();

        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($message);
        $httpClient->expects($this->once())
            ->method('request')
            ->with($this->isInstanceOf(Request::class))
            ->willReturn(new Success($response));

        $publisher = new Publisher($hubUri, $jwtProvider, $httpClient);
        $value = wait($publisher->publish($update));

        $this->assertSame('id', $value);
    }

}