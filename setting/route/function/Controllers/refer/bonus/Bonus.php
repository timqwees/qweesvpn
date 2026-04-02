<?php declare(strict_types=1);
namespace Setting\Route\Function\Controllers\refer\bonus;

use App\Config\Database;
use setting\route\function\Controllers\client\Client;
use Setting\Route\Function\Controllers\vpn\v2ray\Xray;
use setting\route\function\Controllers\refer\config\ReferConfig;

class Bonus
{
    /**
     * Добавляет бонусные дни рефералу при покупке
     * @param string $buyerUniID Уникальный ID покупателя
     * @return array Результат операции с информацией о бонусе
     */
    public function add_bonus_days(string $buyerUniID): array
    {
        // Получаем данные покупателя
        $buyer = Client::get($buyerUniID);
        if (empty($buyer['refer_link'])) {
            return [
                'success' => false,
                'message' => 'У покупателя нет реферальной ссылки',
                'bonus_days' => 0
            ];
        }

        // Находим реферала по реферному коду
        $ref = Database::send(
            "SELECT * FROM qwees_users WHERE refer_link=? LIMIT 1",
            [$buyer['refer_link']]
        );
        
        if (!is_array($ref) || empty($ref[0])) {
            return [
                'success' => false,
                'message' => 'Реферал не найден',
                'bonus_days' => 0
            ];
        }

        $refUniID = $ref[0]['uniID']; // uniID реферала
        $days_buy = intval($buyer['count_days'] ?? 0); // дни покупки клиента

        if ($days_buy <= 0) {
            return [
                'success' => false,
                'message' => 'Количество дней покупки не указано',
                'bonus_days' => 0
            ];
        }

        // Получаем процент бонусных дней из конфигурации
        $percent = ReferConfig::getReferralDaysBonus();
        $bonus = (int) floor($days_buy * $percent / 100); // считаем процент дней от клиента

        if ($bonus <= 0) {
            return [
                'success' => false,
                'message' => "Бонусные дни равны 0 (покупка: {$days_buy} дней, процент: {$percent}%)",
                'bonus_days' => 0
            ];
        }

        // Запускаем обновление через Xray класс
        $xray = new Xray();
        $updateResult = $xray->xui_update($refUniID, $bonus);

        // Логируем успешное начисление бонуса
        file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
            sprintf(
                "[%s] [REFER_BONUS] Реферал %s получил %d бонусных дней от покупки клиента %s (%d дней, %d%%)\n",
                date('Y-m-d H:i:s'),
                $refUniID,
                $bonus,
                $buyerUniID,
                $days_buy,
                $percent
            ),
            FILE_APPEND
        );

        return [
            'success' => $updateResult['status'] === 'ok',
            'message' => $updateResult['message'] ?? 'Ошибка при начислении бонуса',
            'bonus_days' => $bonus,
            'referral_uniid' => $refUniID,
            'buyer_uniid' => $buyerUniID,
            'purchase_days' => $days_buy,
            'bonus_percent' => $percent
        ];
    }
}
