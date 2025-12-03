<?php

declare(strict_types=1);

namespace Rede;

/**
 * Representa informações IATA
 */
class Iata
{
    private string $code;
    private string $departureTax;

    public function __construct(string $code, string $departureTax)
    {
        $this->code = $code;
        $this->departureTax = $departureTax;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'departureTax' => $this->departureTax,
        ];
    }
}

