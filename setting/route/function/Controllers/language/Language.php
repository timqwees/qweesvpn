<?php declare(strict_types=1);
namespace Setting\Route\Function\Controllers\language;

use App\Config\Database;
use App\Config\Session;

class Language
{
    /**
     * Доступные языки
     */
    public const LANGUAGES = [
        'ru' => 'Русский',
        'en' => 'English'
    ];

    /**
     * Получает текущий язык пользователя
     */
    public static function getCurrent(): string
    {
        return Session::init('lang') ?? 'ru';
    }

    /**
     * Получает переводы
     */
    public static function getTranslations(string $language): array
    {
        $translations = [
            'ru' => [
                'main' => 'Главная',
                'profile' => 'Профиль',
                'settings' => 'Настройки',
                'additional' => 'Дополнительное',
                'ping' => 'Пинг',
                'protocol' => 'Протокол',
                'ip_address' => 'IP-адрес',
                'server' => 'Сервер',
                'language' => 'Язык',
                'language_switch' => 'Смена языка',
                'active' => 'Активен',
                'inactive' => 'Неактивен',
                'yes' => 'Да',
                'no' => 'Нет',
                'remaining' => 'Осталось',
                'days' => 'дней',
                'theme' => 'Тема',
                'dark' => 'Темная',
                'light' => 'Светлая'
            ],
            'en' => [
                'main' => 'Main',
                'profile' => 'Profile',
                'settings' => 'Settings',
                'additional' => 'Additional',
                'ping' => 'Ping',
                'protocol' => 'Protocol',
                'ip_address' => 'IP Address',
                'server' => 'Server',
                'language' => 'Language',
                'language_switch' => 'Language Switch',
                'active' => 'Active',
                'inactive' => 'Inactive',
                'yes' => 'Yes',
                'no' => 'No',
                'remaining' => 'Remaining',
                'days' => 'days',
                'theme' => 'Theme',
                'dark' => 'Dark',
                'light' => 'Light'
            ]
        ];

        return $translations[$language] ?? $translations['ru'];
    }
}
