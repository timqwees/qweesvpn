<?php declare(strict_types=1);
namespace Setting\Route\Function\Controllers\refer;

use App\Config\Database;
use Setting\Route\Function\Controllers\client\Client;
use App\Models\Network\Network;
use App\Models\Network\Message;
use setting\route\function\Controllers\refer\config\ReferConfig;

class Refer
{
    private string $reflink = '';

    /**
     * Генерация реферальной ссылки
     *
     * @return string
     */
    public function generationRefer(): string
    {
        $this->reflink = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 7);
        return $this->reflink;
    }

    /**
     * Проверяет наличие уникальной реферальной ссылки у всех пользователей.
     * Если ссылки нет или она пуста, генерирует новую для каждого такого пользователя.
     * Возвращает массив uniID => refer_link для всех пользователей.
     */
    public function globalCheckRefer(): array
    {
        // Получаем всех пользователей с uniID и refer_link
        $sql = Database::send('SELECT uniID, refer_link FROM qwees_users');
        $users = [];
        if (is_array($sql)) {
            if (isset($sql[0]) && is_array($sql[0]) && array_keys($sql[0]) === range(0, count($sql[0]) - 1)) {
                $users = $sql[0];
            } elseif (array_keys($sql) === range(0, count($sql) - 1)) {
                $users = $sql;
            }
        }

        if (empty($users) || !is_array($users)) {
            return [];
        }

        $result = [];

        foreach ($users as $user) {
            $uniID = $user['uniID'];
            $current_link = isset($user['refer_link']) ? $user['refer_link'] : '';
            if (empty($current_link)) {
                $newRefer = $this->generationRefer();
                Database::send('UPDATE qwees_users SET refer_link = ? WHERE uniID = ?', [$newRefer, strval($uniID)]);
                $result[$uniID] = $newRefer;
            } else {
                $result[$uniID] = $current_link;
            }
        }

        return $result;
    }

    /**
     * Активирует реферальную ссылку для пользователя.
     *
     * @param string $myUniID Уникальный ID пользователя, который активирует ссылку
     * @param string $refer_link Реферальная ссылка (без префикса 'ref=')
     * @param string $type Тип перенаправления после успешного активации
     * @param bool $skipRedirect Если true, не выполнять редирект (для использования в Listener)
     * @return bool true, если активация успешна, false в случае ошибки
     */
    public function setRefer(
        string $myUniID,
        string $refer_link,
        string $type = '/profile',
        bool $skipRedirect = false
    ): bool {
        // Проверка: пользователь не может активировать свою собственную ссылку
        $client_user = Client::get($myUniID);

        // ВАЖНО: Проверяем, существует ли пользователь в базе данных
        // Если uniID пустой, значит пользователя нет в БД
        if (empty($client_user['uniID']) || $client_user['uniID'] === '') {
            if (!$skipRedirect) {
                Message::set('refer_error', 'Пользователь не найден. Пожалуйста, сначала зарегистрируйтесь.');
                Network::onRedirect($type);
            }

            // Логируем попытку активации несуществующим пользователем
            file_put_contents(
                $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
                sprintf(
                    "[%s] [ОШИБКА - РЕФЕРАЛ] setRefer: Попытка активации реферальной ссылки несуществующим пользователем uniID=%s, refer_link=%s\n",
                    date('Y-m-d H:i:s'),
                    $myUniID,
                    $refer_link
                ),
                FILE_APPEND
            );

            return false;
        }

        // Уже есть активированная ссылка у пользователя? (не даём заново активировать)
        if (!empty($client_user['refer_link'])) {
            if (!$skipRedirect) {
                Message::set('refer_error', 'Вы уже активировали реферальную ссылку!');
                Network::onRedirect($type);
            }
            return false;
        }

        // ========= Разделяем ссылку вида ref=xxxxxxx и извлекаем id (xxxxxxx)
        if (strpos($refer_link, 'ref=') === 0) {
            $refer_link = substr($refer_link, strlen('ref='));
        }
        // ======================================================

        // Проверка, существует ли реферальная ссылка и чья она
        $result = Database::send('SELECT * FROM qwees_users WHERE refer_link = ? LIMIT 1', [$refer_link]);
        if (!is_array($result) || count($result) === 0) {
            if (!$skipRedirect) {
                Message::set('refer_error', 'Реферальная ссылка не найдена');
                Network::onRedirect($type);
            }
            return false;
        }

        $client_refer = Client::get($result[0]['uniID']);

        // Нельзя активировать свою же ссылку
        if ($client_refer['uniID'] === $myUniID) {
            if (!$skipRedirect) {
                Message::set('refer_error', 'Нельзя использовать свою собственную реферальную ссылку');
                Network::onRedirect($type);
            }
            return false;
        }

        // Нельзя активировать повторно ту же ссылку (дублирование безопасности)
        if (!empty($client_user['refer_link'])) {
            if (!$skipRedirect) {
                Message::set('refer_error', 'Вы уже активировали реферальную ссылку!');
                Network::onRedirect($type);
            }
            return false;
        }

        // Активируем: увеличиваем счетчик рефера и сохраняем для пользователя активированную ссылку
        $updateReferCount = Database::send('UPDATE qwees_users SET refer_count = refer_count + 1 WHERE uniID = ?', [$client_refer['uniID']]);
        
        // Получаем множитель скидки из конфигурации
        $discountMultiplier = ReferConfig::getInvitedDiscountMultiplier();
        
        // Устанавливаем реферальную ссылку и применяем скидку для пользователя
        $updateUserRefer = Database::send(
            'UPDATE qwees_users SET refer_link = ?, amount = amount * ? WHERE uniID = ?', 
            [$refer_link, $discountMultiplier, $myUniID]
        );

        // Проверяем, что обновления прошли успешно
        if ($updateReferCount === false || $updateUserRefer === false) {
            if (!$skipRedirect) {
                Message::set('refer_error', 'Ошибка при активации реферальной ссылки. Попробуйте позже.');
                Network::onRedirect($type);
            }

            // Логируем ошибку обновления
            file_put_contents(
                $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
                sprintf(
                    "[%s] [ОШИБКА - РЕФЕРАЛ] setRefer: Ошибка обновления БД при активации реферальной ссылки. uniID=%s, refer_link=%s\n",
                    date('Y-m-d H:i:s'),
                    $myUniID,
                    $refer_link
                ),
                FILE_APPEND
            );

            return false;
        }

        if (!$skipRedirect) {
            $discountPercent = ReferConfig::getInvitedDiscountPercent();
            Message::set('refer_success', "Реферальная ссылка успешно активирована! Вы получили скидку {$discountPercent}% на все тарифы!");
        }

        //ЛОГИРОВАНИЕ
        $discountPercent = ReferConfig::getInvitedDiscountPercent();
        file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
            sprintf(
                "[%s] [SUCCESS] setRefer: Пользователь с uniID=%s активировал реферальную ссылку '%s' (рефер: uniID=%s). Скидка %d%% применена.\n",
                date('Y-m-d H:i:s'),
                $myUniID,
                $refer_link,
                $client_refer['uniID'],
                $discountPercent
            ),
            FILE_APPEND
        );

        if (!$skipRedirect) {
            Network::onRedirect($type);
        }
        return true;
    }
}