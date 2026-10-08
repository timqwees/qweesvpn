<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Vpn\V2ray;

use Setting\Route\Function\Controllers\Server\Network as ServerNetwork;
use Setting\Route\Function\Functions;

// Транспорт к панели 3x-ui: авторизация (Bearer/cookie+CSRF), HTTP, батчи.
// Выделен из Xray по SRP: здесь только сеть, никакой бизнес-логики клиентов.
// Сервер уже должен быть выбран через ServerNetwork::selectServer($code).

class PanelHttp
{
    /** Настройка текущего сервера из реестра Network. */
    public static function server(string $key): string
    {
        return (string) (ServerNetwork::getServer()[$key] ?? '');
    }

    /** Базовый URL панели 3x-ui текущего сервера, без суффикса /panel/api. */
    private static function panelBase(): string
    {
        return rtrim(self::server('XUI_URL_PANEL'), '/');
    }

    /** Имя cookie сессии после POST /login (если не используется XUI_API_TOKEN). */
    private static function cookieName(): string
    {
        return self::server('XUI_LOGIN_NAME_COOKIE') ?: 'x-ui';
    }

    /** Логин администратора панели (POST /login без Bearer). */
    private static function xuiLogin(): string
    {
        return self::server('XUI_LOGIN');
    }

    /** Пароль администратора панели. */
    private static function xuiPassword(): string
    {
        return self::server('XUI_PASSWORD');
    }

    /** API Token панели (Settings → Security). Пусто — POST /login и cookie + CSRF. */
    private static function xuiApiToken(): string
    {
        return trim(self::server('XUI_API_TOKEN'), " \t\n\r\0\x0B\"'");
    }

    /** Одна сессия cookie (+ CSRF) на HTTP-запрос PHP; сбрасывается при смене сервера. */
    private static ?string $threeXuiCookieCache = null;

    private static ?string $threeXuiCsrfCache = null;

    private static string $threeXuiCacheServer = '';

    /** Базовые SSL/UA опции (int 0/1 — совместимость с curl_setopt_array в PHP 8+). */
    private static function curlSslUserAgentOpts(): array
    {
        return [
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; ' . Functions::site()['ООО'] . '/1.0)',
        ];
    }

    /**
     * @param array<int, mixed> $opts
     */
    private static function curlOptsMerge(array $opts): array
    {
        // array_merge reindexes numeric keys (CURLOPT_* are ints) → invalid keys for curl_setopt_array (PHP 8+).
        return array_replace(self::curlSslUserAgentOpts(), $opts);
    }

    /** Авторизация 3x-ui по паролю (если нет Bearer-токена). */
    private static function threeXuiLoginCookie(): ?string
    {
        $maxRetries = 3;
        $code = 0;
        $cookie = '';
        $cookieName = self::cookieName();
        $cookiePattern = '/Set-Cookie:\s*(3?' . $cookieName . '=[^;]+)/i';

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $loginBody = [
                'username' => self::xuiLogin(),
                'password' => self::xuiPassword(),
            ];
            $otp = trim((string) ($_ENV['XUI_TWO_FACTOR_CODE'] ?? ''), " \t\n\r\0\x0B\"'");
            if ($otp !== '') {
                $loginBody['twoFactorCode'] = $otp;
            }

            $ch = curl_init(self::panelBase() . '/login');
            curl_setopt_array($ch, self::curlOptsMerge([
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($loginBody, JSON_UNESCAPED_UNICODE),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
                CURLOPT_TIMEOUT => 45,
                CURLOPT_CONNECTTIMEOUT => 20,
            ]));
            $response = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);

            if ($code === 200 && is_string($response) && preg_match($cookiePattern, $response, $m)) {
                $headerEnd = strpos($response, "\r\n\r\n");
                $bodyOff = $headerEnd !== false ? $headerEnd + 4 : false;
                if ($bodyOff === false) {
                    $headerEnd = strpos($response, "\n\n");
                    $bodyOff = $headerEnd !== false ? $headerEnd + 2 : false;
                }
                if ($bodyOff !== false && $bodyOff < strlen($response)) {
                    $loginJson = json_decode(substr($response, $bodyOff), true);
                    if (is_array($loginJson) && array_key_exists('success', $loginJson) && $loginJson['success'] !== true) {
                        Xray::log(
                            \sprintf(
                                "[%s] [ПОДПИСКА -> СЕРВЕР] Login отклонён панелью: %s\n",
                                date('Y-m-d H:i:s'),
                                json_encode($loginJson, JSON_UNESCAPED_UNICODE)
                            )
                        );
                        if ($attempt < $maxRetries) {
                            usleep(500000 * $attempt);
                        }
                        continue;
                    }
                }
                $cookie = $m[1];
                break;
            }

            Xray::log(
                \sprintf(
                    "[%s] [ПОДПИСКА -> СЕРВЕР] Login attempt %d/%d failed: HTTP %d, cURL: %s\n",
                    date('Y-m-d H:i:s'),
                    $attempt,
                    $maxRetries,
                    $code,
                    $curlError
                )
            );
            if ($attempt < $maxRetries) {
                usleep(500000 * $attempt);
            }
        }

        return $cookie !== '' ? $cookie : null;
    }

    /** GET /csrf-token — для cookie-сессии на POST нужен заголовок X-CSRF-Token (см. API Docs). */
    private static function threeXuiFetchCsrfToken(string $cookieHeaderValue): ?string
    {
        foreach (['/csrf-token', '/panel/api/csrf-token'] as $path) {
            $ch = curl_init(self::panelBase() . $path);
            curl_setopt_array($ch, self::curlOptsMerge([
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Accept: application/json', 'Cookie: ' . $cookieHeaderValue],
                CURLOPT_TIMEOUT => 20,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]));
            $response = curl_exec($ch);
            if (!is_string($response) || $response === '') {
                continue;
            }
            $decoded = json_decode($response, true);
            if (is_array($decoded) && ($decoded['success'] ?? false) === true && isset($decoded['obj'])) {
                $t = $decoded['obj'];
                if (is_string($t) && $t !== '') {
                    return $t;
                }
            }
        }

        return null;
    }

    /**
     * @return array{0: string, 1: ?string}|false [Cookie: value, csrf или null]
     */
    private static function threeXuiCookieAuthSession(): array|false
    {
        // Кэш действителен только для того сервера, на котором получен
        if (self::$threeXuiCacheServer === self::panelBase() && self::$threeXuiCookieCache !== null) {
            return [self::$threeXuiCookieCache, self::$threeXuiCsrfCache];
        }
        $cookie = self::threeXuiLoginCookie();
        if ($cookie === null) {
            return false;
        }
        self::$threeXuiCacheServer = self::panelBase();
        self::$threeXuiCookieCache = $cookie;
        self::$threeXuiCsrfCache = self::threeXuiFetchCsrfToken($cookie);
        if (self::$threeXuiCsrfCache === null) {
            Xray::log(
                \sprintf("[%s] [ПОДПИСКА -> СЕРВЕР] CSRF токен не получен; POST может быть отклонён панелью\n", date('Y-m-d H:i:s'))
            );
        }

        return [self::$threeXuiCookieCache, self::$threeXuiCsrfCache];
    }

    /**
     * HTTP к 3x-ui: path от корня сайта, например /panel/api/inbounds/list.
     *
     * @return array<string,mixed>|false Декодированный JSON; при ошибке сети/ответа — false
     */
    public static function threeXuiHttp(string $method, string $path, ?array $jsonBody = null): array|false
    {
        $url = self::panelBase() . $path;
        $token = self::xuiApiToken();
        $headers = ['Accept: application/json'];
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        } else {
            $sess = self::threeXuiCookieAuthSession();
            if ($sess === false) {
                return false;
            }
            [$cookieVal, $csrf] = $sess;
            $headers[] = 'Cookie: ' . $cookieVal;
            $methodU = strtoupper($method);
            if (
                $csrf !== null && $csrf !== ''
                && in_array($methodU, ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            ) {
                $headers[] = 'X-CSRF-Token: ' . $csrf;
            }
        }
        if ($jsonBody !== null) {
            $headers[] = 'Content-Type: application/json';
        }

        $ch = curl_init($url);
        $opts = self::curlOptsMerge([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 8,
        ]);
        if (strtoupper($method) === 'POST') {
            $opts[CURLOPT_POST] = true;
            if ($jsonBody !== null) {
                $opts[CURLOPT_POSTFIELDS] = json_encode($jsonBody, JSON_UNESCAPED_UNICODE);
            }
        } elseif (strtoupper($method) !== 'GET') {
            $opts[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
            if ($jsonBody !== null) {
                $opts[CURLOPT_POSTFIELDS] = json_encode($jsonBody, JSON_UNESCAPED_UNICODE);
            }
        }
        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);

        if ($response === false || $curlErr !== '') {
            Xray::log(
                \sprintf("[%s] [ПОДПИСКА -> СЕРВЕР] HTTP %s %s failed: %s\n", date('Y-m-d H:i:s'), $method, $path, $curlErr)
            );
            return false;
        }
        $decoded = json_decode((string) $response, true);
        if (!\is_array($decoded)) {
            Xray::log(
                \sprintf("[%s] [ПОДПИСКА -> СЕРВЕР] Invalid JSON from %s HTTP %d\n", date('Y-m-d H:i:s'), $path, $httpCode)
            );
            return false;
        }

        if ($httpCode >= 400) {
            Xray::log(
                \sprintf(
                    "[%s] [ПОДПИСКА -> СЕРВЕР] %s %s HTTP %d body: %s\n",
                    date('Y-m-d H:i:s'),
                    $method,
                    $path,
                    $httpCode,
                    substr((string) $response, 0, 500)
                )
            );
        }

        return $decoded;
    }

    /**
     * Параллельный пакет GET-запросов к панели (curl_multi) — дашборд одним заходом.
     * @param string[] $paths пути от корня панели, напр. ['/panel/api/server/status']
     * @return array<string,array|false> [path => декодированный JSON или false]
     */
    public static function panelBatch(array $paths): array
    {
        $paths = array_values(array_unique($paths));
        $out = array_fill_keys($paths, false);
        if ($paths === []) return $out;
        $token = self::xuiApiToken();
        if ($token === '') {//без токена — cookie-сессия, идём последовательно старым путём
            foreach ($paths as $p) $out[$p] = self::threeXuiHttp('GET', $p);
            return $out;
        }
        $mh = curl_multi_init();
        $handles = [];
        foreach ($paths as $p) {
            $ch = curl_init(self::panelBase() . $p);
            curl_setopt_array($ch, self::curlOptsMerge([
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Accept: application/json', 'Authorization: Bearer ' . $token],
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 8,
            ]));
            curl_multi_add_handle($mh, $ch);
            $handles[$p] = $ch;
        }
        $running = 0;
        do {
            $mrc = curl_multi_exec($mh, $running);
            if ($running) curl_multi_select($mh, 1.0);
        } while ($running && $mrc === CURLM_OK);
        foreach ($handles as $p => $ch) {
            $body = curl_multi_getcontent($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $d = json_decode((string) $body, true);
            $out[$p] = (\is_array($d) && $code < 400) ? $d : false;
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);
        return $out;
    }
}
