<?php

declare(strict_types=1);

namespace Rede;

/**
 * Representa os ambientes disponíveis da API eRede
 */
class Environment
{
    private string $apiUrl;

    private function __construct(string $apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    public static function production(): self
    {
        return new self('https://api.userede.com.br/erede');
    }

    public static function sandbox(): self
    {
        return new self('https://sandbox-erede.useredecloud.com.br');
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }
}
