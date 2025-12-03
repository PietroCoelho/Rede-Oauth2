<?php

declare(strict_types=1);

namespace Rede\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface para cliente HTTP
 */
interface HttpClientInterface
{
    /**
     * Envia uma requisição HTTP
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws HttpException
     */
    public function send(RequestInterface $request): ResponseInterface;
}

