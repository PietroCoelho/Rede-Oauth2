<?php

declare(strict_types=1);

namespace Rede;

/**
 * Representa uma URL de callback
 */
class Url
{
    public const THREE_D_SECURE_SUCCESS = 'THREE_D_SECURE_SUCCESS';
    public const THREE_D_SECURE_FAILURE = 'THREE_D_SECURE_FAILURE';

    private string $url;
    private string $kind;

    public function __construct(string $url, string $kind)
    {
        $this->url = $url;
        $this->kind = $kind;
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'kind' => $this->kind,
        ];
    }
}

