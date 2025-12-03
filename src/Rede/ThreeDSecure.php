<?php

declare(strict_types=1);

namespace Rede;

/**
 * Representa informações 3DS Secure
 */
class ThreeDSecure
{
    private Device $device;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    public function toArray(): array
    {
        return [
            'device' => $this->device->toArray(),
        ];
    }
}

