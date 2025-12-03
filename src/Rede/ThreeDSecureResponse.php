<?php

declare(strict_types=1);

namespace Rede;

/**
 * Representa a resposta do 3DS Secure
 */
class ThreeDSecureResponse
{
    private ?string $url = null;
    private ?string $method = null;
    private ?array $parameters = null;

    public function __construct(array $data)
    {
        $this->url = $data['url'] ?? null;
        $this->method = $data['method'] ?? null;
        $this->parameters = $data['parameters'] ?? null;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }
}

