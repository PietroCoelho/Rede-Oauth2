<?php

declare(strict_types=1);

namespace Rede\OAuth;

/**
 * Representa um token OAuth
 */
class Token
{
    private string $accessToken;
    private string $tokenType;
    private int $expiresIn;
    private ?int $expiresAt = null;

    public function __construct(
        string $accessToken,
        string $tokenType = 'Bearer',
        int $expiresIn = 3600
    ) {
        $this->accessToken = $accessToken;
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
        $this->expiresAt = time() + $expiresIn;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    public function getExpiresAt(): ?int
    {
        return $this->expiresAt;
    }

    public function isExpired(): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        // Considera expirado se faltar menos de 60 segundos
        return ($this->expiresAt - 60) < time();
    }

    public function toAuthorizationHeader(): string
    {
        return sprintf('%s %s', $this->tokenType, $this->accessToken);
    }
}

