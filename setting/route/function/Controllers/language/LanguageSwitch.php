<?php declare(strict_types=1);
namespace Setting\Route\Function\Controllers\language;

use Setting\Route\Function\Controllers\language\Language;
use App\Config\Session;

class LanguageSwitch
{
    /**
     * Обрабатывает смену языка
     */
    public static function switch()
    {
        $newLang = $_POST['language'] ?? 'ru';
        
        // Проверяем что язык поддерживается
        if (!array_key_exists($newLang, Language::LANGUAGES)) {
            $newLang = 'ru';
        }
        
        Session::init('lang', $newLang);
        
        // Возвращаем JSON ответ для frontend
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'language' => $newLang]);
        exit;
    }
}
