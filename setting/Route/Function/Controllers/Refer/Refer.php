<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Refer;

use App\Config\Database;
use App\Config\Session;
use App\Models\Network\Network;
use Setting\Route\Function\Controllers\Refer\Config\ReferConfig;
use Setting\Route\Function\Controllers\Refer\Bonus\Bonus;
use Setting\Route\Function\Controllers\Client\GetUser;

/**
 * Refer - Корневой файл реферальной системы (как главный класс плагина)
 * 
 * Логика:
 * 1. Через ввод кода → activateRefer() POST /api/referral/activate
 * 2. Через ссылку /reflink={code} → GET маршрут вызывает тот же activateRefer()
 */
class Refer
{

    /**
     * Внутренняя логика активации (используется API и handleReferLink)
     */
    private static function doActivate(string $code, int $userId, GetUser $user): array
    {
        $code = trim(strtoupper($code));

        if (empty($code)) {
            return ['status' => false, 'error' => 'Пожалуйста, введите код'];
        }

        if (!empty($user->getRefer())) {
            return ['status' => false, 'error' => 'Реферальный код уже активирован'];
        }

        if ($code === $user->getMyRefer()) {
            return ['status' => false, 'error' => 'Заприщено использовать свою реферальную ссылку!'];
        }

        $referrer = Database::send("SELECT id FROM qwees_users WHERE myrefer = ?", [$code]);
        if (!is_array($referrer) || $referrer === [] || !isset($referrer[0]['id'])) {
            return ['status' => false, 'error' => 'Реферальный код не найден!'];
        }

        $referrerId = (int) $referrer[0]['id'];
        if ($referrerId === $userId) {
            return ['status' => false, 'error' => 'Нельзя использовать свой реферальный код!'];
        }

        Database::send("UPDATE qwees_users SET refer = ?, refer_id = ? WHERE id = ?", [$code, $referrerId, $userId]);

        $bonus = new Bonus();
        $bonus->giveToNewReferral($userId, $referrerId);
        $bonus->giveToReferrer($referrerId, $userId);

        return ['status' => true, 'message' => 'Реферальный код успешно активирован'];
    }

    /**
     * Генерация уникального реферального кода для нового пользователя
     */
    public function generationRefer(): string
    {
        $pattern = ReferConfig::getCodePattern();//QWE#####

        do {
            $code = '';
            for ($i = 0; $i < strlen($pattern); $i++) {
                $code .= $pattern[$i] === '#' ? chr(mt_rand(48, 90)) : $pattern[$i];//0-9/A-Z/<=>:;?@
            }
        } while (Database::send("SELECT id FROM qwees_users WHERE myrefer = ?", [$code]));

        return $code;
    }

    /**
     * Установка реферального кода для нового пользователя (после регистрации)
     * Работает напрямую с БД, не требует авторизации
     */
    public function setRefer(string $uniID, string $code, bool $silent = true): array
    {
        // получаем свой id и refer
        $user = Database::send("SELECT id, refer FROM qwees_users WHERE uniID = ?", [$uniID]);

        if (!is_array($user) || $user === [] || !isset($user[0]['id'])) {
            return ['status' => false, 'error' => 'Пользователь не найден'];
        }

        if (!empty($user[0]['refer'])) {
            return ['status' => false, 'error' => 'Реферальный код уже активирован'];
        }

        $code = trim(strtoupper($code));
        // получаем id реферала
        $referrer = Database::send("SELECT id FROM qwees_users WHERE myrefer = ?", [$code]);

        // если не нашли такого реферала
        if (!is_array($referrer) || $referrer === [] || !isset($referrer[0]['id'])) {
            return ['status' => false, 'error' => 'Реферальный код не найден'];
        }

        // итоговые данные
        $userId = (int) $user[0]['id'];//свой id
        $referrerId = (int) $referrer[0]['id']; //реферала id

        // записываем реферала
        Database::send("UPDATE qwees_users SET refer = ?, refer_id = ? WHERE id = ?", [$code, $referrerId, $userId]);

        // начисляем бонусы
        $bonus = new Bonus();
        $bonus->giveToNewReferral($userId, $referrerId);//для себя бонус
        $bonus->giveToReferrer($referrerId, $userId);//для реферела бонус

        return ['status' => true, 'message' => 'Реферальный код успешно активирован'];
    }

    /**
     * Обработка перехода по реферальной ссылке /reflink={code}
     * Маршрутизатор перехватывает и вызывает эту функцию с параметром code
     */
    public function onValidateCode(string|null $code = null, string|null $online = null)
    {
        if (empty($code)) {
            if (!($online === 'on')) {
                Network::onRedirect('/');
            }
            header('Content-Type: application/json');
            echo json_encode([
                'status' => false,
                'message' => 'Пожалуйста, введите код'
            ]);
            exit;
        }

        Session::init('pending_refer_code', $code);

        // Если пользователь уже авторизован - активируем сразу
        $user = new GetUser();
        if ($user->getID() > 0) {
            $result = self::doActivate($code, $user->getID(), $user);
            $status = $result['status'] ? 'success' : 'error';
            $msg = $result['error'] ?? $result['message'] ?? '';
            if (!($online === 'on')) {
                Network::onRedirect("/?ref_status={$status}&ref_msg={$msg}");
            } else {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => $result['status'],
                    'message' => $msg
                ]);
                exit;
            }
        }

        // Если не авторизован - редиректим на регистрацию
        // Код уже в сессии, активируется после регистрации
        if (!($online === 'on')) {
            Network::onRedirect('/auth/regist');
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => false,
            'message' => 'Требуется авторизация для активации реферального кода'
        ]);
        exit;
    }
}
