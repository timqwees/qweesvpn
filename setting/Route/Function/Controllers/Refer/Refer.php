<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Refer;

use App\Config\Database;
use App\Config\Session;
use App\Models\Network\Network;
use Setting\Route\Function\Controllers\Admin\Admin;
use Setting\Route\Function\Controllers\Refer\Bonus\Bonus;
use Setting\Route\Function\Controllers\Refer\Config\ReferConfig;
use Setting\Route\Function\Controllers\Refer\ReferInterface\{InterfaceRefer, InterfaceReferLog};
use Setting\Route\Function\Controllers\Refer\Tools\{ReferLog, ReferRepository};
use Setting\Route\Function\Controllers\Client\GetUser;

//SOLID: фасад реферальной системы (как Groups в Admin/Group).
//Вся SQL — в ReferRepository, бонусы — в Bonus, логи — в ReferLog.
//Здесь только проверки и orchestration. Один путь активации для API и регистрации.

class Refer implements InterfaceRefer
{
    private ReferRepository $repo;
    private InterfaceReferLog $log;
    private Bonus $bonus;

    public function __construct(?ReferRepository $repo = null, ?InterfaceReferLog $log = null, ?Bonus $bonus = null)
    {
        $this->log = $log ?? new ReferLog();
        $this->repo = $repo ?? new ReferRepository($this->log);
        $this->bonus = $bonus ?? new Bonus($this->repo, $this->log);
    }

    /**
     * Единый путь активации чужого кода (API и регистрация идут сюда).
     * @return array{status:bool,message?:string,error?:string}
     */
    public function activate(string $code, int $userId): array
    {
        if (!ReferConfig::isEnabled()) {
            return ['status' => false, 'error' => 'Реферальная система отключена'];
        }

        $code = trim(strtoupper($code));

        if ($code === '') {
            return ['status' => false, 'error' => 'Пожалуйста, введите код'];
        }

        $me = $this->repo->findUserById($userId);
        if ($me === null) {
            return ['status' => false, 'error' => 'Пользователь не найден'];
        }

        if (!empty($me['refer'])) {
            return ['status' => false, 'error' => 'Реферальный код уже активирован'];
        }

        if ($code === (string) ($me['myrefer'] ?? '')) {
            return ['status' => false, 'error' => 'Запрещено использовать свою реферальную ссылку!'];
        }

        $referrer = $this->repo->findUserByCode($code);
        if ($referrer === null) {
            $this->log->log('АКТИВАЦИЯ-ОШИБКА', ['user_id' => $userId, 'code' => $code, 'reason' => 'код не найден']);
            return ['status' => false, 'error' => 'Реферальный код не найден!'];
        }

        $referrerId = (int) $referrer['id'];
        if ($referrerId === $userId) {
            return ['status' => false, 'error' => 'Нельзя использовать свой реферальный код!'];
        }

        // Вся привязка — одним коммитом: либо всё записалось, либо ничего
        $result = Database::transaction(function () use ($userId, $code, $referrerId, $me, $referrer) {
            if (!$this->repo->bindReferral($userId, $code, $referrerId)) {
                $this->log->log('АКТИВАЦИЯ-ОШИБКА', ['user_id' => $userId, 'code' => $code, 'reason' => 'привязка не подтверждена']);
                return ['status' => false, 'error' => 'Не удалось привязать код, попробуйте позже'];
            }

            $newBonus = $this->bonus->giveToNewReferral($userId, $referrerId);
            $refBonus = $this->bonus->giveToReferrer($referrerId, $userId);

            $takes = ReferConfig::getReferrerBonus()['takes'];
            if (!$this->repo->recordReferral([
                'referrer_id' => $referrerId,
                'referrer_uniID' => (string) ($referrer['uniID'] ?? ''),
                'referral_id' => $userId,
                'referral_uniID' => (string) ($me['uniID'] ?? ''),
                'code' => $code,
                'days_to_referral' => $newBonus['days'],
                'days_to_referrer' => $refBonus['days'],
                'discount_percent' => $newBonus['discount'],
                'takes_left' => $takes,
            ])) {
                $this->log->log('АКТИВАЦИЯ-ОШИБКА', ['user_id' => $userId, 'code' => $code, 'reason' => 'история не записалась']);
                return false;//откат всей привязки
            }
            $this->repo->backfillHistory($takes);

            $this->log->log('АКТИВАЦИЯ', [
                'user_id' => $userId,
                'referral_uniID' => (string) ($me['uniID'] ?? ''),
                'code' => $code,
                'referrer_id' => $referrerId,
                'referrer_uniID' => (string) ($referrer['uniID'] ?? ''),
            ]);

            return ['status' => true, 'message' => 'Реферальный код успешно активирован'];
        });
        if ($result === false) {
            return ['status' => false, 'error' => 'Не удалось записать, попробуйте позже'];
        }
        return $result;
    }

    /**
     * Установка реферального кода для нового пользователя (после регистрации).
     * Работает напрямую с БД, не требует авторизации.
     */
    public function setRefer(string $uniID, string $code): array
    {
        $user = $this->repo->findUserByUniID($uniID);
        if ($user === null) {
            return ['status' => false, 'error' => 'Пользователь не найден'];
        }
        return $this->activate($code, (int) $user['id']);
    }

    public function generateCode(): string
    {
        $prefix = ReferConfig::getCodePrefix();
        $alphabet = ReferConfig::getCodeAlphabet();
        $alphabetLen = strlen($alphabet);
        $tailLen = max(1, ReferConfig::getCodeLength() - strlen($prefix));

        do {
            $code = $prefix;
            for ($i = 0; $i < $tailLen; $i++) {
                $code .= $alphabet[random_int(0, $alphabetLen - 1)];
            }
        } while ($this->repo->findUserByCode($code) !== null);

        return $code;
    }

    public function getMyReferrals(int $userId, int $limit = 50): array
    {
        $rows = $this->repo->listReferrals($userId, $limit);
        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'name' => trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? '')),
                'email' => self::maskEmail((string) ($row['email'] ?? '')),
                'date' => (string) ($row['created_at'] ?? ''),
            ];
        }
        return $out;
    }

    /**
     * Начислить пригласившему % с покупки приглашённого.
     * Вызывается из Kassa после успешной выдачи подписки.
     */
    public function rewardReferrerFromPurchase(string $buyerUniID, int $boughtDays): array
    {
        return $this->bonus->grantPercentDays($buyerUniID, $boughtDays);
    }

    /**
     * Обработка перехода по реферальной ссылке /reflink={code}.
     * Тонкий web-адаптер: HTTP — здесь, бизнес-логика — в activate().
     */
    public function onValidateCode(?string $code = null, ?string $online = null): void
    {
        $isOnline = ($online === 'on');

        if (empty($code)) {
            if (!$isOnline) {
                Network::onRedirect('/');
            }
            self::json(false, 'Пожалуйста, введите код');
        }

        $code = trim(strtoupper((string) $code));
        Session::init('pending_refer_code', $code);

        // Если пользователь уже авторизован - активируем сразу
        $user = new GetUser();
        if ($user->getID() > 0) {
            $result = $this->activate($code, $user->getID());
            $msg = (string) ($result['error'] ?? $result['message'] ?? '');
            if (!$isOnline) {
                Network::onRedirect('/?ref_status=' . ($result['status'] ? 'success' : 'error') . '&ref_msg=' . urlencode($msg));
            }
            self::json((bool) $result['status'], $msg);
        }

        // Если не авторизован - редиректим на регистрацию
        // Код уже в сессии, активируется после регистрации
        if (!$isOnline) {
            Network::onRedirect('/auth/regist');
        }
        self::json(false, 'Требуется авторизация для активации реферального кода');
    }

    /**
     * Сохранение настроек из админки (POST /admin/refer/save, как Gifts::onSave).
     */
    public function onSave(): void
    {
        $url = (string) ($_POST['url'] ?? '/admin');
        ReferConfig::save([
            'enabled' => ($_POST['enabled'] ?? '') === 'on',
            'referral_days' => $_POST['referral_days'] ?? null,
            'referral_discount' => $_POST['referral_discount'] ?? null,
            'discount_uses' => $_POST['discount_uses'] ?? null,
            'referrer_days' => $_POST['referrer_days'] ?? null,
            'referrer_percent' => $_POST['referrer_percent'] ?? null,
            'referrer_takes' => $_POST['referrer_takes'] ?? null,
        ]);
        $c = ReferConfig::getAll();
        (new Admin())->LoggerCRM(
            'сохранил рефералку: ' . ($c['enabled'] ? 'вкл' : 'выкл')
            . ', приглашённый +' . $c['referral_days'] . ' дн / -' . $c['referral_discount'] . '% на ' . $c['discount_uses'] . ' пок.'
            . ', реферер +' . $c['referrer_days'] . ' дн / ' . $c['referrer_percent'] . '% с покупки ×' . $c['referrer_takes'] . ' раз'
        );
        Network::onRedirect($url);
    }

    private static function json(bool $status, string $message): void
    {
        header('Content-Type: application/json');
        echo json_encode(['status' => $status, 'message' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2 || $parts[0] === '') {
            return '';
        }
        return mb_substr($parts[0], 0, 1) . '***@' . $parts[1];
    }
}
