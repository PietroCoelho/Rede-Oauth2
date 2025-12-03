<?php

declare(strict_types=1);

namespace Rede;

use GuzzleHttp\Psr7\Request;
use Rede\Http\AuthenticatedHttpClient;
use Rede\Http\HttpClientInterface;
use Rede\Http\HttpException;
use Rede\OAuth\OAuthClient;

/**
 * Cliente principal do SDK eRede
 */
class eRede
{
    private Store $store;
    private HttpClientInterface $httpClient;

    public function __construct(Store $store, ?HttpClientInterface $httpClient = null)
    {
        $this->store = $store;

        if ($httpClient === null) {
            $oauthClient = $store->getOAuthClient();
            if ($oauthClient === null) {
                $tokenEndpoint = $store->getEnvironment()->getApiUrl() . '/oauth2/token';
                $oauthClient = new OAuthClient($tokenEndpoint);
            }
            $httpClient = new AuthenticatedHttpClient($store, $oauthClient);
        }

        $this->httpClient = $httpClient;
    }

    /**
     * Cria uma nova transação
     *
     * @param Transaction $transaction
     * @return TransactionResponse
     * @throws HttpException
     */
    public function create(Transaction $transaction): TransactionResponse
    {
        try {
            $url = $this->store->getEnvironment()->getApiUrl() . '/v2/transactions';
            $body = json_encode($transaction->toArray());
            $request = new Request('POST', $url, [], $body);
            $response = $this->httpClient->send($request);
            $responseData = json_decode((string) $response->getBody(), true);

            if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
                $errorMessage = $responseData['message'] ?? $responseData['returnMessage'] ?? 'Erro desconhecido';
                throw new HttpException(
                    'Erro ao criar transação: ' . $errorMessage,
                    $response->getStatusCode()
                );
            }
            return new TransactionResponse($responseData);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new HttpException('Erro ao criar transação: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Captura uma transação
     *
     * @param Transaction $transaction
     * @return TransactionResponse
     * @throws HttpException
     */
    public function capture(Transaction $transaction): TransactionResponse
    {
        try {
            if ($transaction->getTid() === null) {
                throw new HttpException('TID é obrigatório para captura');
            }

            $url = $this->store->getEnvironment()->getApiUrl() . '/v2/transactions/' . $transaction->getTid();
            $body = json_encode([
                'amount' => (int) round($transaction->getAmount() * 100),
            ]);

            $request = new Request('PUT', $url, [], $body);
            $response = $this->httpClient->send($request);

            $responseData = json_decode((string) $response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                $errorMessage = $responseData['message'] ?? $responseData['returnMessage'] ?? 'Erro desconhecido';
                throw new HttpException(
                    'Erro ao capturar transação: ' . $errorMessage,
                    $response->getStatusCode()
                );
            }

            return new TransactionResponse($responseData);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new HttpException('Erro ao capturar transação: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Cancela uma transação
     *
     * @param Transaction $transaction
     * @return TransactionResponse
     * @throws HttpException
     */
    public function cancel(Transaction $transaction): TransactionResponse
    {
        try {
            if ($transaction->getTid() === null) {
                throw new HttpException('TID é obrigatório para cancelamento');
            }

            $url = $this->store->getEnvironment()->getApiUrl() . '/v2/transactions/' . $transaction->getTid() . '/refunds';
            $body = json_encode([
                'amount' => $transaction->getAmount() > 0
                    ? (int) round($transaction->getAmount() * 100)
                    : null,
            ]);

            $request = new Request('POST', $url, [], $body);
            $response = $this->httpClient->send($request);
            $responseData = json_decode((string) $response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                $errorMessage = $responseData['message'] ?? $responseData['returnMessage'] ?? 'Erro desconhecido';
                throw new HttpException(
                    'Erro ao cancelar transação: ' . $errorMessage,
                    $response->getStatusCode()
                );
            }

            return new TransactionResponse($responseData);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new HttpException('Erro ao cancelar transação: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Consulta uma transação pelo TID
     *
     * @param string $tid
     * @return TransactionResponse
     * @throws HttpException
     */
    public function get(string $tid): TransactionResponse
    {
        try {
            $url = $this->store->getEnvironment()->getApiUrl() . '/v2/transactions/' . $tid;

            $request = new Request('GET', $url);
            $response = $this->httpClient->send($request);
            $responseData = json_decode((string) $response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                $errorMessage = $responseData['message'] ?? $responseData['returnMessage'] ?? 'Erro desconhecido';
                throw new HttpException(
                    'Erro ao consultar transação: ' . $errorMessage,
                    $response->getStatusCode()
                );
            }

            return new TransactionResponse($responseData);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new HttpException('Erro ao consultar transação: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Consulta uma transação pela referência
     *
     * @param string $reference
     * @return TransactionResponse
     * @throws HttpException
     */
    public function getByReference(string $reference): TransactionResponse
    {
        try {
            $url = $this->store->getEnvironment()->getApiUrl() . '/v2/transactions?reference=' . urlencode($reference);

            $request = new Request('GET', $url);
            $response = $this->httpClient->send($request);

            $responseData = json_decode((string) $response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                $errorMessage = $responseData['message'] ?? $responseData['returnMessage'] ?? 'Erro desconhecido';
                throw new HttpException(
                    'Erro ao consultar transação: ' . $errorMessage,
                    $response->getStatusCode()
                );
            }

            // Se retornar uma lista, pega o primeiro item
            if (isset($responseData[0])) {
                $responseData = $responseData[0];
            }

            return new TransactionResponse($responseData);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new HttpException('Erro ao consultar transação por referência: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Consulta cancelamentos de uma transação
     *
     * @param string $tid
     * @return TransactionResponse
     * @throws HttpException
     */
    public function getRefunds(string $tid): TransactionResponse
    {
        try {
            $url = $this->store->getEnvironment()->getApiUrl() . '/v2/transactions/' . $tid . '/refunds';

            $request = new Request('GET', $url);
            $response = $this->httpClient->send($request);

            $responseData = json_decode((string) $response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                $errorMessage = $responseData['message'] ?? $responseData['returnMessage'] ?? 'Erro desconhecido';
                throw new HttpException(
                    'Erro ao consultar cancelamentos: ' . $errorMessage,
                    $response->getStatusCode()
                );
            }

            return new TransactionResponse($responseData);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new HttpException('Erro ao consultar cancelamentos: ' . $e->getMessage(), 0, $e);
        }
    }
}
