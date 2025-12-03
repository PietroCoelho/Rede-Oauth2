<?php

declare(strict_types=1);

namespace Rede;

/**
 * Representa um cartão de crédito
 */
class CreditCard
{
    private string $cardNumber;
    private string $securityCode;
    private string $expirationMonth;
    private string $expirationYear;
    private string $holderName;

    public function __construct(
        string $cardNumber,
        string $securityCode,
        string $expirationMonth,
        string $expirationYear,
        string $holderName
    ) {
        $this->cardNumber = $cardNumber;
        $this->securityCode = $securityCode;
        $this->expirationMonth = $expirationMonth;
        $this->expirationYear = $expirationYear;
        $this->holderName = $holderName;
    }

    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function getSecurityCode(): string
    {
        return $this->securityCode;
    }

    public function getExpirationMonth(): string
    {
        return $this->expirationMonth;
    }

    public function getExpirationYear(): string
    {
        return $this->expirationYear;
    }

    public function getHolderName(): string
    {
        return $this->holderName;
    }

    public function toArray(): array
    {
        return [
            'cardNumber' => $this->cardNumber,
            'securityCode' => $this->securityCode,
            'expirationMonth' => $this->expirationMonth,
            'expirationYear' => $this->expirationYear,
            'holderName' => $this->holderName,
        ];
    }
}

