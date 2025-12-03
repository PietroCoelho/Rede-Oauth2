<?php

declare(strict_types=1);

namespace Rede;

use Rede\OAuth\OAuthClientInterface;

/**
 * Representa uma loja/configuração da eRede
 */
class Store
{
    private string $filiation;
    private string $token;
    private Environment $environment;
    private ?OAuthClientInterface $oauthClient;

    public function __construct(
        string $filiation,
        string $token,
        Environment $environment,
        ?OAuthClientInterface $oauthClient = null
    ) {
        $this->filiation = $filiation;
        $this->token = $token;
        $this->environment = $environment;
        $this->oauthClient = $oauthClient;
    }

    public function getFiliation(): string
    {
        return $this->filiation;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    public function getOAuthClient(): ?OAuthClientInterface
    {
        return $this->oauthClient;
    }
}

