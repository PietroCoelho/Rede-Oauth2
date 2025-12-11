<?php

declare(strict_types=1);

namespace Rede\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Rede\Environment;
use Rede\eRede;
use Rede\OAuth\OAuthClient;
use Rede\Store;
use Rede\Transaction;

/**
 * Testes de integração reais com a API eRede
 * 
 * Para executar estes testes, configure as credenciais de sandbox:
 * - merchantId (client_id)
 * - merchantKey (client_secret)
 * 
 * @group integration
 * @group real-api
 */
class RedeGatewayTest extends TestCase
{
    private Store $store;
    private eRede $erede;

    protected function setUp(): void
    {
        parent::setUp();

        $merchantId = $_ENV['REDE_MERCHANT_ID'];
        $merchantKey = $_ENV['REDE_MERCHANT_KEY'];
        $oauthEndpoint = $_ENV['REDE_SANDBOX_OAUTH_ENDPOINT']
            ?? 'https://rl7-sandbox-api.useredecloud.com.br/oauth2/token';

        if (empty($merchantId) || empty($merchantKey)) {
            $this->markTestSkipped(
                'Credenciais não configuradas. Configure REDE_MERCHANT_ID e REDE_MERCHANT_KEY no arquivo .env'
            );
        }

        $oauthClient = new OAuthClient($oauthEndpoint);
        $this->store = new Store(
            $merchantId,
            $merchantKey,
            Environment::sandbox(),
            $oauthClient
        );
        $this->erede = new eRede($this->store);
    }

    /**
     * Testa a obtenção de token OAuth
     */
    public function testOAuthTokenRetrieval(): void
    {
        $oauthClient = $this->store->getOAuthClient();
        $this->assertNotNull($oauthClient);

        $merchantId = $_ENV['REDE_MERCHANT_ID'] ?? getenv('REDE_MERCHANT_ID');
        $merchantKey = $_ENV['REDE_MERCHANT_KEY'] ?? getenv('REDE_MERCHANT_KEY');

        $token = $oauthClient->getAccessToken($merchantId, $merchantKey);
        $this->assertNotEmpty($token->getAccessToken());
        $this->assertEquals('Bearer', $token->getTokenType());
        $this->assertFalse($token->isExpired());
        $this->assertGreaterThan(0, $token->getExpiresIn());
    }

    /**
     * Testa a criação de uma transação de crédito
     */
    public function testCreateCreditCardTransaction(): void
    {
        $transaction = (new Transaction(10.00, time() . rand(1000000000, 9999999999)))
            ->creditCard(
                '5448280000000007',
                '235',
                '12',
                '2025',
                'JOHN SNOW'
            )
            ->capture(false);

        $response = $this->erede->create($transaction);
        $this->assertNotNull($response->getTid());
        $this->assertNotNull($response->getReturnCode());
        $this->assertNotNull($response->getAuthorization());

        if ($response->getReturnCode() === '00') {
            $this->assertEquals('Approved', $response->getAuthorization()->getStatus());
        }
    }

    /**
     * Testa a criação de uma transação com captura automática
     */
    public function testCreateTransactionWithCapture(): void
    {
        $transaction = (new Transaction(15.50, time() . rand(1000000000, 9999999999)))
            ->creditCard(
                '5448280000000007',
                '235',
                '12',
                '2025',
                'JOHN SNOW'
            )
            ->capture(true);

        $response = $this->erede->create($transaction);

        $this->assertNotNull($response->getTid());
        $this->assertNotNull($response->getReturnCode());
    }

    /**
     * Testa a criação de uma transação parcelada
     */
    public function testCreateTransactionWithInstallments(): void
    {
        $transaction = (new Transaction(100.00, time() . rand(1000000000, 9999999999)))
            ->creditCard(
                '5448280000000007',
                '235',
                '12',
                '2025',
                'JOHN SNOW'
            )
            ->setInstallments(3)
            ->capture(true);

        $response = $this->erede->create($transaction);

        $this->assertNotNull($response->getTid());
        $this->assertEquals(3, $response->getInstallments());
        $this->assertNotNull($response->getReturnCode());
    }

    /**
     * Testa a consulta de uma transação pelo TID
     */
    public function testGetTransactionByTid(): void
    {
        // Primeiro cria uma transação
        $transaction = (new Transaction(20.00, time() . rand(1000000000, 9999999999)))
            ->creditCard(
                '5448280000000007',
                '235',
                '12',
                '2025',
                'JOHN SNOW'
            )
            ->capture(true);

        $createResponse = $this->erede->create($transaction);
        $this->assertNotNull($createResponse->getTid());

        // Depois consulta pelo TID
        $tid = $createResponse->getTid();
        $queryResponse = $this->erede->get($tid);

        $this->assertEquals($tid, $queryResponse->getTid());
        $this->assertNotNull($queryResponse->getAuthorization());
    }

    /**
     * Testa a consulta de uma transação pela referência
     */
    public function testGetTransactionByReference(): void
    {
        $reference = time() . rand(1000000000, 9999999999);

        // Primeiro cria uma transação
        $transaction = (new Transaction(25.00, $reference))
            ->creditCard(
                '5448280000000007',
                '235',
                '12',
                '2025',
                'JOHN SNOW'
            )
            ->capture(true);

        $createResponse = $this->erede->create($transaction);
        $this->assertNotNull($createResponse->getTid());

        // Aguarda um pouco para garantir que a transação foi processada
        sleep(1);

        // Depois consulta pel referência
        $queryResponse = $this->erede->getByReference($createResponse->getReference());
        $this->assertEquals(substr($reference, 0, 16), $queryResponse->getReference());
        $this->assertNotNull($queryResponse->getTid());
    }

    /**
     * Testa a captura de uma transação pré-autorizada
     */
    public function testCapturePreAuthorizedTransaction(): void
    {
        // Primeiro cria uma transação sem captura
        $transaction = (new Transaction(30.00, time() . rand(1000000000, 9999999999)))
            ->creditCard(
                '5448280000000007',
                '235',
                '12',
                '2025',
                'JOHN SNOW'
            )
            ->capture(false);

        $createResponse = $this->erede->create($transaction);
        $this->assertNotNull($createResponse->getTid());
        $this->assertEquals('00', $createResponse->getReturnCode());

        // Depois captura a transação
        $captureTransaction = (new Transaction(30.00))->setTid($createResponse->getTid());
        $captureResponse = $this->erede->capture($captureTransaction);

        $this->assertEquals($createResponse->getTid(), $captureResponse->getTid());
        $this->assertEquals('00', $captureResponse->getReturnCode());
    }

    /**
     * Testa o cancelamento de uma transação
     */
    public function testCancelTransaction(): void
    {
        // Primeiro cria e captura uma transação
        $transaction = (new Transaction(35.00, time() . rand(1000000000, 9999999999)))
            ->creditCard(
                '5448280000000007',
                '235',
                '12',
                '2025',
                'JOHN SNOW'
            )
            ->capture(true);

        $createResponse = $this->erede->create($transaction);
        $this->assertNotNull($createResponse->getTid());
        $this->assertEquals('00', $createResponse->getReturnCode());

        // Aguarda um pouco
        sleep(1);

        // Depois cancela a transação
        $cancelTransaction = (new Transaction(35.00))->setTid($createResponse->getTid());
        $cancelResponse = $this->erede->cancel($cancelTransaction);

        $this->assertEquals($createResponse->getTid(), $cancelResponse->getTid());
        // Código 359 significa cancelamento realizado com sucesso
        $this->assertContains($cancelResponse->getReturnCode(), ['359', '00']);
    }

    /**
     * Testa a criação de uma transação com soft descriptor
     * (desenvolvimento em andamento)
    public function testCreateTransactionWithSoftDescriptor(): void
    {
        $transaction = (new Transaction(20.99, time() . rand(1000000000, 9999999999)))
            ->creditCard(
                '5448280000000007',
                '235',
                '12',
                '2025',
                'JOHN SNOW'
            )
            ->setSoftDescriptor('Loja X');

        $response = $this->erede->create($transaction);
        $this->assertEquals('00', $response->getReturnCode());
    }
     */

    /**
     * Testa a criação de uma transação com informações adicionais de gateway e módulo
     * (desenvolvimento em andamento)
    public function testCreateTransactionWithAdditionalGatewayAndModule(): void
    {
        $transaction = (new Transaction(20.99, time() . rand(1000000000, 9999999999)))
            ->creditCard(
                '5448280000000007',
                '235',
                '12',
                '2025',
                'JOHN SNOW'
            )
            ->additional(1234, 56);

        $response = $this->erede->create($transaction);
        $this->assertEquals('00', $response->getReturnCode());
    }
     */

    /**
     * Testa a criação de uma transação com MCC dinâmico
     * (desenvolvimento em andamento)
    public function testCreateTransactionWithDynamicMCC(): void
    {
        $transaction = (new Transaction(20.99, time() . rand(1000000000, 9999999999)))
            ->creditCard(
                '5448280000000007',
                '235',
                '12',
                '2025',
                'JOHN SNOW'
            )
            ->mcc(
                'LOJADOZE',
                '22349202212',
                new SubMerchant(
                    '1234',
                    'São Paulo',
                    'Brasil'
                )
            );

        $response = $this->erede->create($transaction);
        $this->assertEquals('00', $response->getReturnCode());
    }
     */

    /**
     * Testa a criação de uma transação com IATA
     * (desenvolvimento em andamento)
    public function testCreateTransactionWithIATA(): void
    {
        $transaction = (new Transaction(20.99, time() . rand(1000000000, 9999999999)))
            ->creditCard(
                '5448280000000007',
                '235',
                '12',
                '2025',
                'JOHN SNOW'
            )
            ->iata('101010', '250');

        $response = $this->erede->create($transaction);
        $this->assertEquals('00', $response->getReturnCode());
    }
     */

    /**
     * Testa uma transação zero dollar (validação de cartão)
     */
    public function testCreateZeroDollarTransaction(): void
    {
        $transaction = (new Transaction(0, time() . rand(1000000000, 9999999999)))
            ->creditCard(
                '5448280000000007',
                '235',
                '12',
                '2025',
                'JOHN SNOW'
            );

        $response = $this->erede->create($transaction);
        // Código 174 é esperado para zero dollar
        $this->assertContains($response->getReturnCode(), ['174', '00']);
    }

    /**
     * Testa a criação de uma transação de débito com autenticação 3DS
     * (desenvolvimento em andamento)
    public function testCreateDebitCardTransactionWithAuthentication(): void
    {
        $transaction = (new Transaction(25.00, time() . rand(1000000000, 9999999999)))
            ->debitCard(
                '5277696455399733',
                '123',
                '12',
                '2025',
                'JOHN SNOW'
            );

        $device = new Device(
            1,
            'BROWSER',
            false,
            'BR',
            500,
            500,
            3
        );

        $transaction->threeDSecure($device);
        $transaction->addUrl('https://redirecturl.com/3ds/success', Url::THREE_D_SECURE_SUCCESS);
        $transaction->addUrl('https://redirecturl.com/3ds/failure', Url::THREE_D_SECURE_FAILURE);

        $response = $this->erede->create($transaction);
        $returnCode = $response->getReturnCode();

        $this->assertContains($returnCode, ['220', '201', '00']);

        if ($returnCode === '220' && $response->getThreeDSecure() !== null) {
            $this->assertNotEmpty($response->getThreeDSecure()->getUrl());
        }
    }
     */


    /**
 * Testa a consulta de cancelamentos (refunds) de uma transação
 * (desenvolvimento em andamento)
    public function testConsultTransactionRefunds(): void
    {
        // Primeiro cria uma transação
        $transaction = (new Transaction(20.99, time() . rand(1000000000, 9999999999)))
            ->creditCard(
                '5448280000000007',
                '235',
                '12',
                '2025',
                'JOHN SNOW'
            )
            ->capture(true);

        $createResponse = $this->erede->create($transaction);
        $this->assertEquals('00', $createResponse->getReturnCode());

        // Aguarda um pouco
        sleep(1);

        // Cancela a transação
        $cancelTransaction = (new Transaction(20.99))->setTid($createResponse->getTid());
        $cancelResponse = $this->erede->cancel($cancelTransaction);
        $this->assertContains($cancelResponse->getReturnCode(), ['359', '00']);

        // Aguarda um pouco para garantir que o cancelamento foi processado
        sleep(1);

        // Consulta os cancelamentos
        $refundsResponse = $this->erede->getRefunds($createResponse->getTid());
        $this->assertNotNull($refundsResponse);
        $this->assertNotNull($refundsResponse->getReturnCode());
    }
 */
}
