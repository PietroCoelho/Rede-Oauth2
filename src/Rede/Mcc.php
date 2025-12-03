<?php

declare(strict_types=1);

namespace Rede;

/**
 * Representa MCC dinâmico
 */
class Mcc
{
    private string $establishmentName;
    private string $mcc;
    private SubMerchant $subMerchant;

    public function __construct(string $establishmentName, string $mcc, SubMerchant $subMerchant)
    {
        $this->establishmentName = $establishmentName;
        $this->mcc = $mcc;
        $this->subMerchant = $subMerchant;
    }

    public function toArray(): array
    {
        return [
            'establishmentName' => $this->establishmentName,
            'mcc' => $this->mcc,
            'subMerchant' => $this->subMerchant->toArray(),
        ];
    }
}

