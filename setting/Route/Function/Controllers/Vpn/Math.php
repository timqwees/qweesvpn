<?php

declare(strict_types=1);//cтрогая типизация

namespace Setting\Route\Function\Controllers\Vpn;
use DateTime, DateTimeZone;

class Math
{

    public static function canculateDay(mixed $data_end)
    {
        if (empty($data_end))
            return ['status' => 'off', 'showDate' => 'Нет подписки', 'time_left' => 0];

        $time_left = strtotime($data_end) - new DateTime('now', new DateTimeZone('Europe/Moscow'))->getTimestamp();

        if ($time_left <= 0)
            return ['status' => 'off', 'showDate' => 'Истекла', 'time_left' => 0];

        $days = floor($time_left / 86400);
        $hours = floor(($time_left % 86400) / 3600);
        $minutes = floor(($time_left % 3600) / 60);

        $showDate = $time_left < 86400 ?
            (date('Y-m-d') === date('Y-m-d', strtotime($data_end)) ? "Сегодня до " . date('H:i', strtotime($data_end)) : "Завтра до " . date('H:i', strtotime($data_end))) :
            'до ' . date('d.m.Y', strtotime($data_end));

        return ['status' => 'on', 'showDate' => $showDate, 'time_left' => $time_left];
    }

    public static function calculateEndDate(mixed $days)
    {
        if (empty($days) || $days <= 0)
            return ['status' => 'error', 'message' => 'Некорректное количество дней'];

        $current_time = new DateTime('now', new DateTimeZone('Europe/Moscow'))->getTimestamp();
        $end_time = strtotime("+$days days", $current_time);

        return [
            'status' => 'success',
            'current_date' => date('Y-m-d H:i:s', $current_time),
            'end_date' => date('Y-m-d H:i:s', $end_time),
            'days_added' => $days,
            'time_left_seconds' => $end_time - $current_time
        ];
    }
}