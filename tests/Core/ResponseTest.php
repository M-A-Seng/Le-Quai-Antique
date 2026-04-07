<?php

namespace App\Tests\Core;

use App\Core\Response;
use App\Exceptions\ServerException;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testInvalidHttpCodeException()
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage("Code HTTP invalide : 2000");
        new Response('content', 2000, ['Content-Type' => 'text/html']);
    }

    public function testInvalidHeadersException()
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessageMatches('/Données invalides :/');
        new Response('content', 200, ['text/html']);
    }

    public function testResponseSuccess()
    {
        $response = new Response('content', 226, ['Content-Type' => 'text/html']);
        $this->assertEquals('content', $response->getContent());
        $this->assertEquals(226, $response->getStatus());
        $this->assertEquals(['Content-Type' => 'text/html'], $response->getHeaders());
    }
}