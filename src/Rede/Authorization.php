<?php

declare(strict_types=1);

namespace Rede;

/**
 * Representa a autorização de uma transação
 */
class Authorization
{
    private string $status;
    private ?string $returnCode = null;
    private ?string $returnMessage = null;
    private ?string $tid = null;
    private ?string $nsu = null;
    private ?string $authorizationCode = null;

    public function __construct(array $data)
    {
        $this->status = $data['status'] ?? '';
        $this->returnCode = $data['returnCode'] ?? null;
        $this->returnMessage = $data['returnMessage'] ?? null;
        $this->tid = $data['tid'] ?? null;
        $this->nsu = $data['nsu'] ?? null;
        $this->authorizationCode = $data['authorizationCode'] ?? null;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getReturnCode(): ?string
    {
        return $this->returnCode;
    }

    public function getReturnMessage(): ?string
    {
        return $this->returnMessage;
    }

    public function getTid(): ?string
    {
        return $this->tid;
    }

    public function getNsu(): ?string
    {
        return $this->nsu;
    }

    public function getAuthorizationCode(): ?string
    {
        return $this->authorizationCode;
    }
}

