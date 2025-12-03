<?php

declare(strict_types=1);

namespace Rede\Tests\Unit\OAuth;

use PHPUnit\Framework\TestCase;
use Rede\OAuth\Token;

class TokenTest extends TestCase
{
    public function testTokenCreation(): void
    {
        $token = new Token('access_token_123', 'Bearer', 3600);

        $this->assertEquals('access_token_123', $token->getAccessToken());
        $this->assertEquals('Bearer', $token->getTokenType());
        $this->assertEquals(3600, $token->getExpiresIn());
        $this->assertNotNull($token->getExpiresAt());
    }

    public function testTokenIsNotExpired(): void
    {
        $token = new Token('access_token_123', 'Bearer', 3600);

        $this->assertFalse($token->isExpired());
    }

    public function testTokenIsExpired(): void
    {
        $token = new Token('access_token_123', 'Bearer', -100);

        $this->assertTrue($token->isExpired());
    }

    public function testToAuthorizationHeader(): void
    {
        $token = new Token('access_token_123', 'Bearer', 3600);

        $this->assertEquals('Bearer access_token_123', $token->toAuthorizationHeader());
    }
}

