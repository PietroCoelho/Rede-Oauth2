<?php

declare(strict_types=1);

namespace Rede\Tests\Unit\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Rede\Environment;
use Rede\Http\AuthenticatedHttpClient;
use Rede\OAuth\OAuthClient;
use Rede\OAuth\Token;
use Rede\Store;

class AuthenticatedHttpClientTest extends TestCase
{
    public function testSendRequestWithValidToken(): void
    {
        // Mock do token OAuth
        $token = new Token('test_token', 'Bearer', 3600);
        $mockOAuthClient = $this->createMock(OAuthClient::class);
        $mockOAuthClient->method('getAccessToken')
            ->willReturn($token);

        // Mock da resposta HTTP
        $mockResponse = new Response(200, [], json_encode(['success' => true]));
        $mockHandler = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);

        $store = new Store('PV123', 'TOKEN123', Environment::production(), $mockOAuthClient);
        $authenticatedClient = new AuthenticatedHttpClient($store, $mockOAuthClient, $httpClient);

        $request = new Request('GET', 'https://api.test.com/test');
        $response = $authenticatedClient->send($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSendRequestWithExpiredToken(): void
    {
        // Mock do token OAuth expirado
        $expiredToken = new Token('expired_token', 'Bearer', -100);
        $newToken = new Token('new_token', 'Bearer', 3600);

        $mockOAuthClient = $this->createMock(OAuthClient::class);
        $mockOAuthClient->method('getAccessToken')
            ->willReturn($newToken);

        // Mock da resposta HTTP
        $mockResponse = new Response(200, [], json_encode(['success' => true]));
        $mockHandler = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);

        $store = new Store('PV123', 'TOKEN123', Environment::production(), $mockOAuthClient);
        $authenticatedClient = new AuthenticatedHttpClient($store, $mockOAuthClient, $httpClient);

        $request = new Request('GET', 'https://api.test.com/test');
        $response = $authenticatedClient->send($request);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
