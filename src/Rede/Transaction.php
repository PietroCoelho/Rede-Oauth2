<?php

declare(strict_types=1);

namespace Rede;

/**
 * Representa uma transação
 */
class Transaction
{
    private int|float $amount;
    private string $reference;
    private ?string $tid = null;
    private ?CreditCard $creditCard = null;
    private ?DebitCard $debitCard = null;
    private bool $capture = true;
    private ?int $installments = null;
    private ?int $gatewayId = null;
    private ?int $moduleId = null;
    private ?Mcc $mcc = null;
    private ?Iata $iata = null;
    private ?ThreeDSecure $threeDSecure = null;
    private array $urls = [];
    private ?string $softDescriptor = null;

    public function __construct(int|float $amount, string $reference = '')
    {
        $this->amount = $amount;

        // Se a referência foi fornecida, sanitiza e valida
        if (!empty($reference)) {
            // Remove caracteres especiais e mantém apenas alfanumérico
            // A API eRede aceita apenas alfanumérico na referência
            $this->reference = preg_replace('/[^a-zA-Z0-9]/', '', $reference);

            // Valida o tamanho máximo (geralmente 16 caracteres para eRede)
            if (strlen($this->reference) > 16) {
                $this->reference = substr($this->reference, 0, 16);
            }
        } else {
            // Para captura/cancelamento, a referência não é necessária
            $this->reference = '';
        }
    }

    public function creditCard(
        string $cardNumber,
        string $securityCode,
        string $expirationMonth,
        string $expirationYear,
        string $holderName
    ): self {
        $this->creditCard = new CreditCard(
            $cardNumber,
            $securityCode,
            $expirationMonth,
            $expirationYear,
            $holderName
        );
        return $this;
    }

    public function debitCard(
        string $cardNumber,
        string $securityCode,
        string $expirationMonth,
        string $expirationYear,
        string $holderName
    ): self {
        $this->debitCard = new DebitCard(
            $cardNumber,
            $securityCode,
            $expirationMonth,
            $expirationYear,
            $holderName
        );
        return $this;
    }

    public function capture(bool $capture): self
    {
        $this->capture = $capture;
        return $this;
    }

    public function setInstallments(int $installments): self
    {
        $this->installments = $installments;
        return $this;
    }

    public function setSoftDescriptor(string $softDescriptor): self
    {
        $this->softDescriptor = $softDescriptor;
        return $this;
    }

    public function additional(int $gatewayId, int $moduleId): self
    {
        $this->gatewayId = $gatewayId;
        $this->moduleId = $moduleId;
        return $this;
    }

    public function mcc(string $establishmentName, string $mcc, SubMerchant $subMerchant): self
    {
        $this->mcc = new Mcc($establishmentName, $mcc, $subMerchant);
        return $this;
    }

    public function iata(string $code, string $departureTax): self
    {
        $this->iata = new Iata($code, $departureTax);
        return $this;
    }

    public function threeDSecure(Device $device): self
    {
        $this->threeDSecure = new ThreeDSecure($device);
        return $this;
    }

    public function addUrl(string $url, string $type): self
    {
        $this->urls[] = new Url($url, $type);
        return $this;
    }

    public function setTid(string $tid): self
    {
        $this->tid = $tid;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getTid(): ?string
    {
        return $this->tid;
    }

    public function getCreditCard(): ?CreditCard
    {
        return $this->creditCard;
    }

    public function getDebitCard(): ?DebitCard
    {
        return $this->debitCard;
    }

    public function isCapture(): bool
    {
        return $this->capture;
    }

    public function getInstallments(): ?int
    {
        return $this->installments;
    }

    public function getGatewayId(): ?int
    {
        return $this->gatewayId;
    }

    public function getModuleId(): ?int
    {
        return $this->moduleId;
    }

    public function getMcc(): ?Mcc
    {
        return $this->mcc;
    }

    public function getIata(): ?Iata
    {
        return $this->iata;
    }

    public function getThreeDSecure(): ?ThreeDSecure
    {
        return $this->threeDSecure;
    }

    public function getUrls(): array
    {
        return $this->urls;
    }

    public function toArray(): array
    {
        $data = [
            'amount' => (int) round($this->amount * 100),
            'capture' => $this->capture,
        ];

        if (!empty($this->reference)) {
            $data['reference'] = $this->reference;
        }

        // Dados do cartão no nível raiz (formato correto da API eRede)
        if ($this->creditCard !== null) {
            $data['kind'] = 'credit';
            $data['cardNumber'] = $this->creditCard->getCardNumber();
            $data['cardholderName'] = $this->creditCard->getHolderName();
            $data['securityCode'] = $this->creditCard->getSecurityCode();
            $data['expirationMonth'] = (int) $this->creditCard->getExpirationMonth();
            $data['expirationYear'] = (int) $this->creditCard->getExpirationYear();
        }

        if ($this->debitCard !== null) {
            $data['kind'] = 'debit';
            $data['cardNumber'] = $this->debitCard->getCardNumber();
            $data['cardholderName'] = $this->debitCard->getHolderName();
            $data['securityCode'] = $this->debitCard->getSecurityCode();
            $data['expirationMonth'] = (int) $this->debitCard->getExpirationMonth();
            $data['expirationYear'] = (int) $this->debitCard->getExpirationYear();
        }

        if ($this->installments !== null) {
            $data['installments'] = $this->installments;
        }

        if ($this->softDescriptor !== null) {
            $data['softDescriptor'] = $this->softDescriptor;
        }

        if ($this->gatewayId !== null && $this->moduleId !== null) {
            $data['additional'] = [
                'gatewayId' => $this->gatewayId,
                'moduleId' => $this->moduleId,
            ];
        }

        if ($this->mcc !== null) {
            $data['mcc'] = $this->mcc->toArray();
        }

        if ($this->iata !== null) {
            $data['iata'] = $this->iata->toArray();
        }

        if ($this->threeDSecure !== null) {
            $data['threeDSecure'] = $this->threeDSecure->toArray();
        }

        if (!empty($this->urls)) {
            $data['urls'] = array_map(fn(Url $url) => $url->toArray(), $this->urls);
        }

        return $data;
    }
}
