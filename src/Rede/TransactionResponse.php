<?php

declare(strict_types=1);

namespace Rede;

/**
 * Representa a resposta de uma transação
 */
class TransactionResponse
{
    private ?string $tid = null;
    private ?string $reference = null;
    private ?int $amount = null;
    private ?Authorization $authorization = null;
    private ?ThreeDSecureResponse $threeDSecure = null;
    private ?string $returnCode = null;
    private ?string $returnMessage = null;
    private ?string $dateTime = null;
    private ?int $installments = null;
    private ?string $cardBin = null;
    private ?string $last4 = null;

    public function __construct(array $data)
    {
        // A API pode retornar dados de duas formas:
        // 1. Criação: dados no nível raiz
        // 2. Consulta: dados dentro de um objeto 'authorization'

        if (isset($data['authorization']) && is_array($data['authorization'])) {
            // Formato de consulta (get) - dados dentro de authorization
            $authData = $data['authorization'];
            $this->tid = $authData['tid'] ?? null;
            $this->reference = $authData['reference'] ?? null;
            $this->amount = isset($authData['amount']) ? (int) $authData['amount'] : null;
            $this->returnCode = $authData['returnCode'] ?? null;
            $this->returnMessage = $authData['returnMessage'] ?? null;
            $this->dateTime = $authData['dateTime'] ?? null;
            $this->installments = isset($authData['installments']) ? (int) $authData['installments'] : null;
            $this->cardBin = $authData['cardBin'] ?? null;
            $this->last4 = $authData['last4'] ?? null;

            $this->authorization = new Authorization($authData);
        } else {
            // Formato de criação - dados no nível raiz
            $this->tid = $data['tid'] ?? null;
            $this->reference = $data['reference'] ?? null;
            $this->amount = isset($data['amount']) ? (int) $data['amount'] : null;
            $this->returnCode = $data['returnCode'] ?? null;
            $this->returnMessage = $data['returnMessage'] ?? null;
            $this->dateTime = $data['dateTime'] ?? null;
            $this->installments = isset($data['installments']) ? (int) $data['installments'] : null;
            $this->cardBin = $data['cardBin'] ?? null;
            $this->last4 = $data['last4'] ?? null;

            $authorizationData = [
                'tid' => $data['tid'] ?? null,
                'nsu' => $data['nsu'] ?? null,
                'authorizationCode' => $data['authorizationCode'] ?? null,
                'returnCode' => $data['returnCode'] ?? null,
                'returnMessage' => $data['returnMessage'] ?? null,
                'status' => $this->determineStatus($data['returnCode'] ?? null),
            ];
            $this->authorization = new Authorization($authorizationData);
        }

        if (isset($data['threeDSecure'])) {
            $this->threeDSecure = new ThreeDSecureResponse($data['threeDSecure']);
        }
    }

    /**
     * Determina o status baseado no returnCode
     */
    private function determineStatus(?string $returnCode): string
    {
        if ($returnCode === null) {
            return '';
        }

        // Código 00 significa sucesso/aprovado
        if ($returnCode === '00') {
            return 'Approved';
        }

        // Outros códigos podem ser mapeados conforme necessário
        return 'Pending';
    }

    public function getTid(): ?string
    {
        return $this->tid;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function getAuthorization(): ?Authorization
    {
        return $this->authorization;
    }

    public function getThreeDSecure(): ?ThreeDSecureResponse
    {
        return $this->threeDSecure;
    }

    public function getReturnCode(): ?string
    {
        return $this->returnCode;
    }

    public function getReturnMessage(): ?string
    {
        return $this->returnMessage;
    }

    public function getDateTime(): ?string
    {
        return $this->dateTime;
    }

    public function getInstallments(): ?int
    {
        return $this->installments;
    }

    public function getCardBin(): ?string
    {
        return $this->cardBin;
    }

    public function getLast4(): ?string
    {
        return $this->last4;
    }
}
