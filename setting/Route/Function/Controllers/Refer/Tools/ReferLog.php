<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Refer\Tools;

use Setting\Route\Function\Controllers\Refer\ReferInterface\InterfaceReferLog;

//SOLID: SRP — только логирование реферальной системы (как Tools/Save в Admin/Group)

class ReferLog implements InterfaceReferLog
{
    public function log(string $event, array $context = []): void
    {
        $parts = [];
        foreach ($context as $key => $value) {
            $parts[] = $key . '=' . (\is_scalar($value) ? (string) $value : (string) json_encode($value, JSON_UNESCAPED_UNICODE));
        }
        file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'qwees.log',
            \sprintf(
                "[%s] [РЕФЕРАЛ:%s]%s\n",
                date('Y-m-d H:i:s'),
                $event,
                $parts !== [] ? ' ' . implode(' ', $parts) : ''
            ),
            FILE_APPEND
        );
    }
}
