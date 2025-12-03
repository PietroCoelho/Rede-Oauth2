<?php

declare(strict_types=1);

namespace Rede\Tests\Unit\OAuth;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Rede\OAuth\OAuthClient;
use Rede\OAuth\OAuthException;

class OAuthClientTest extends TestCase
{
    public function testGetAccessTokenSuccess(): void
    {
        $mockResponse = new Response(200, [], json_encode([
            'access_token' => 'test_token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]));

        $mockHandler = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);

        $oauthClient = new OAuthClient('https://api.test.com/oauth/token', $httpClient);
        $token = $oauthClient->getAccessToken('client_id', 'client_secret');

        $this->assertEquals('test_token', $token->getAccessToken());
        $this->assertEquals('Bearer', $token->getTokenType());
        $this->assertEquals(3600, $token->getExpiresIn());
    }

    public function testGetAccessTokenError(): void
    {
        $this->expectException(OAuthException::class);

        $mockResponse = new Response(400, [], json_encode([
            'error' => 'invalid_client',
            'error_description' => 'Invalid client credentials',
        ]));

        $mockHandler = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);

        $oauthClient = new OAuthClient('https://api.test.com/oauth/token', $httpClient);
        $oauthClient->getAccessToken('client_id', 'client_secret');
    }

    public function testGetAccessTokenInvalidResponse(): void
    {
        $this->expectException(OAuthException::class);

        $mockResponse = new Response(200, [], 'invalid json');

        $mockHandler = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);

        $oauthClient = new OAuthClient('https://api.test.com/oauth/token', $httpClient);
        $oauthClient->getAccessToken('client_id', 'client_secret');
    }
}

