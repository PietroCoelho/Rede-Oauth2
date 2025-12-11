<?php

declare(strict_types=1);

namespace Rede\Http;

use GuzzleHttp\Client;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Rede\OAuth\OAuthClientInterface;
use Rede\OAuth\Token;
use Rede\Store;

/**
 * Cliente HTTP autenticado com OAuth
 */
class AuthenticatedHttpClient implements HttpClientInterface
{
    private ClientInterface $httpClient;
    private OAuthClientInterface $oauthClient;
    private Store $store;
    private ?Token $token = null;

    public function __construct(
        Store $store,
        OAuthClientInterface $oauthClient,
        ?ClientInterface $httpClient = null
    ) {
        $this->store = $store;
        $this->oauthClient = $oauthClient;
        $this->httpClient = $httpClient ?? new Client();
    }

    /**
     * Envia uma requisição HTTP autenticada
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws HttpException
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        // Garante que temos um token válido
        $this->ensureValidToken();
        $request = $request->withHeader('Authorization', $this->token->toAuthorizationHeader());
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withHeader('Accept', 'application/json');

        try {
            $response = $this->httpClient->sendRequest($request);

            // Se receber 401, tenta renovar o token e reenvia
            if ($response->getStatusCode() === 401) {
                $this->token = null;
                $this->ensureValidToken();
                $request = $request->withHeader('Authorization', $this->token->toAuthorizationHeader());
                $response = $this->httpClient->sendRequest($request);
            }

            return $response;
        } catch (ClientExceptionInterface $e) {
            throw new HttpException('Erro ao enviar requisição HTTP: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Garante que temos um token válido
     *
     * @return void
     * @throws HttpException
     */
    private function ensureValidToken(): void
    {
        if ($this->token === null || $this->token->isExpired()) {
            try {
                $this->token = $this->oauthClient->getAccessToken(
                    $this->store->getFiliation(),
                    $this->store->getToken()
                );
            } catch (\Rede\OAuth\OAuthException $e) {
                throw new HttpException('Erro ao obter token OAuth: ' . $e->getMessage(), 0, $e);
            }
        }
    }
}
