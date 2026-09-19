<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Refer\ReferInterface;

//SOLID: ISP — контракт логирования реферальной системы

interface InterfaceReferLog
{
    /**
     * Записать событие: [РЕФЕРАЛ:СОБЫТИЕ] key=value ...
     */
    public function log(string $event, array $context = []): void;
}
