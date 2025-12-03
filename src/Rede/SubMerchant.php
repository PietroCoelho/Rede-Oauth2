<?php

declare(strict_types=1);

namespace Rede;

/**
 * Representa um sub-merchant para MCC dinâmico
 */
class SubMerchant
{
    private string $mcc;
    private string $city;
    private string $country;

    public function __construct(string $mcc, string $city, string $country)
    {
        $this->mcc = $mcc;
        $this->city = $city;
        $this->country = $country;
    }

    public function toArray(): array
    {
        return [
            'mcc' => $this->mcc,
            'city' => $this->city,
            'country' => $this->country,
        ];
    }
}

