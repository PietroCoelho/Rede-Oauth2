<?php

declare(strict_types=1);

namespace Rede\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Rede\CreditCard;
use Rede\Device;
use Rede\Iata;
use Rede\Mcc;
use Rede\SubMerchant;
use Rede\ThreeDSecure;
use Rede\Transaction;
use Rede\Url;

class TransactionTest extends TestCase
{
    public function testTransactionCreation(): void
    {
        $transaction = new Transaction(20.99, 'pedido123');

        $this->assertEquals(20.99, $transaction->getAmount());
        $this->assertEquals('pedido123', $transaction->getReference());
        $this->assertTrue($transaction->isCapture());
    }

    public function testCreditCard(): void
    {
        $transaction = (new Transaction(20.99, 'pedido123'))
            ->creditCard('5448280000000007', '235', '12', '2020', 'John Snow');

        $creditCard = $transaction->getCreditCard();
        $this->assertInstanceOf(CreditCard::class, $creditCard);
    }

    public function testDebitCard(): void
    {
        $transaction = (new Transaction(20.99, 'pedido123'))
            ->debitCard('5448280000000007', '235', '12', '2020', 'John Snow');

        $debitCard = $transaction->getDebitCard();
        $this->assertInstanceOf(\Rede\DebitCard::class, $debitCard);
    }

    public function testCapture(): void
    {
        $transaction = (new Transaction(20.99, 'pedido123'))->capture(false);

        $this->assertFalse($transaction->isCapture());
    }

    public function testInstallments(): void
    {
        $transaction = (new Transaction(20.99, 'pedido123'))->setInstallments(3);

        $this->assertEquals(3, $transaction->getInstallments());
    }

    public function testAdditional(): void
    {
        $transaction = (new Transaction(20.99, 'pedido123'))->additional(1234, 56);

        $this->assertEquals(1234, $transaction->getGatewayId());
        $this->assertEquals(56, $transaction->getModuleId());
    }

    public function testMcc(): void
    {
        $subMerchant = new SubMerchant('1234', 'São Paulo', 'Brasil');
        $transaction = (new Transaction(20.99, 'pedido123'))
            ->mcc('LOJADOZE', '22349202212', $subMerchant);

        $mcc = $transaction->getMcc();
        $this->assertInstanceOf(Mcc::class, $mcc);
    }

    public function testIata(): void
    {
        $transaction = (new Transaction(20.99, 'pedido123'))
            ->iata('code123', '250');

        $iata = $transaction->getIata();
        $this->assertInstanceOf(Iata::class, $iata);
    }

    public function testThreeDSecure(): void
    {
        $device = new Device(1, 'BROWSER', false, 'BR', 500, 500, 3);
        $transaction = (new Transaction(20.99, 'pedido123'))
            ->threeDSecure($device);

        $threeDSecure = $transaction->getThreeDSecure();
        $this->assertInstanceOf(ThreeDSecure::class, $threeDSecure);
    }

    public function testUrls(): void
    {
        $transaction = (new Transaction(20.99, 'pedido123'))
            ->addUrl('https://example.com/success', Url::THREE_D_SECURE_SUCCESS)
            ->addUrl('https://example.com/failure', Url::THREE_D_SECURE_FAILURE);

        $urls = $transaction->getUrls();
        $this->assertCount(2, $urls);
        $this->assertInstanceOf(Url::class, $urls[0]);
    }

    public function testToArray(): void
    {
        $transaction = (new Transaction(20.99, 'pedido123'))
            ->creditCard('5448280000000007', '235', '12', '2020', 'John Snow')
            ->capture(false)
            ->setInstallments(3);

        $array = $transaction->toArray();
        $this->assertEquals(2099, $array['amount']);
        $this->assertEquals('pedido123', $array['reference']);
        $this->assertFalse($array['capture']);
        $this->assertEquals(3, $array['installments']);
    }
}
