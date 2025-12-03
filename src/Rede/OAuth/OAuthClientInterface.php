<?php

declare(strict_types=1);

namespace Rede\OAuth;

/**
 * Interface para cliente OAuth
 */
interface OAuthClientInterface
{
    /**
     * Obtém um token de acesso válido
     *
     * @param string $clientId
     * @param string $clientSecret
     * @return Token
     * @throws OAuthException
     */
    public function getAccessToken(string $clientId, string $clientSecret): Token;

    /**
     * Renova um token de acesso
     *
     * @param Token $token
     * @param string $clientId
     * @param string $clientSecret
     * @return Token
     * @throws OAuthException
     */
    public function refreshToken(Token $token, string $clientId, string $clientSecret): Token;
}

