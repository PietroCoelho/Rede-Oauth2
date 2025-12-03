<?php

declare(strict_types=1);

namespace Rede\OAuth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Cliente OAuth 2.0 para autenticação com a API eRede
 */
class OAuthClient implements OAuthClientInterface
{
    private string $tokenEndpoint;
    private Client $httpClient;
    private LoggerInterface $logger;

    public function __construct(
        string $tokenEndpoint,
        ?Client $httpClient = null,
        ?LoggerInterface $logger = null
    ) {
        $this->tokenEndpoint = $tokenEndpoint;
        $this->httpClient = $httpClient ?? new Client();
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Obtém um token de acesso usando client credentials
     *
     * @param string $clientId
     * @param string $clientSecret
     * @return Token
     * @throws OAuthException
     */
    public function getAccessToken(string $clientId, string $clientSecret): Token
    {
        try {
            $this->logger->info('Solicitando token de acesso OAuth', [
                'endpoint' => $this->tokenEndpoint,
            ]);
            $response = $this->httpClient->request('POST', $this->tokenEndpoint, [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($clientId . ':' . $clientSecret),
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);

            $body = (string) $response->getBody();
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new OAuthException('Resposta inválida do servidor OAuth: ' . json_last_error_msg());
            }

            if ($response->getStatusCode() !== 200) {
                $error = $data['error'] ?? 'unknown_error';
                $errorDescription = $data['error_description'] ?? 'Erro desconhecido';
                throw new OAuthException(
                    sprintf('Erro ao obter token: %s - %s', $error, $errorDescription)
                );
            }

            if (!isset($data['access_token'])) {
                throw new OAuthException('Token de acesso não encontrado na resposta');
            }

            $token = new Token(
                $data['access_token'],
                $data['token_type'] ?? 'Bearer',
                (int) ($data['expires_in'] ?? 3600)
            );

            $this->logger->info('Token de acesso obtido com sucesso', [
                'expires_in' => $token->getExpiresIn(),
            ]);

            return $token;
        } catch (GuzzleException $e) {
            $this->logger->error('Erro ao solicitar token OAuth', [
                'message' => $e->getMessage(),
            ]);
            throw new OAuthException('Erro ao comunicar com servidor OAuth: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Renova um token de acesso
     *
     * @param Token $token
     * @param string $clientId
     * @param string $clientSecret
     * @return Token
     * @throws OAuthException
     */
    public function refreshToken(Token $token, string $clientId, string $clientSecret): Token
    {
        // Por enquanto, apenas obtém um novo token
        // Pode ser implementado refresh token se a API suportar
        return $this->getAccessToken($clientId, $clientSecret);
    }
}
