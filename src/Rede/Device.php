<?php

declare(strict_types=1);

namespace Rede;

/**
 * Representa informações do dispositivo para 3DS
 */
class Device
{
    private int $colorDepth;
    private string $deviceType3ds;
    private bool $javaEnabled;
    private string $language;
    private int $screenHeight;
    private int $screenWidth;
    private int $timeZoneOffset;

    public function __construct(
        int $colorDepth,
        string $deviceType3ds,
        bool $javaEnabled,
        string $language,
        int $screenHeight,
        int $screenWidth,
        int $timeZoneOffset
    ) {
        $this->colorDepth = $colorDepth;
        $this->deviceType3ds = $deviceType3ds;
        $this->javaEnabled = $javaEnabled;
        $this->language = $language;
        $this->screenHeight = $screenHeight;
        $this->screenWidth = $screenWidth;
        $this->timeZoneOffset = $timeZoneOffset;
    }

    public function toArray(): array
    {
        return [
            'colorDepth' => $this->colorDepth,
            'deviceType3ds' => $this->deviceType3ds,
            'javaEnabled' => $this->javaEnabled,
            'language' => $this->language,
            'screenHeight' => $this->screenHeight,
            'screenWidth' => $this->screenWidth,
            'timeZoneOffset' => $this->timeZoneOffset,
        ];
    }
}

