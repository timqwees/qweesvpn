<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Admin\Overview;

use App\Config\Database;
use Setting\Route\Function\Controllers\Admin\Finance\Finance;
use Setting\Route\Function\Controllers\Server\Network as ServerNetwork;
use Setting\Route\Function\Controllers\Vpn\V2ray\Xray;
use DateTime, DateTimeZone;

// Живой дашборд админки: состояние панелей 3x-ui + агрегаты из БД.
// Упрощённая модель: единый сбор + единый кэш (60 сек). Health — часть overview,
// а не отдельный движок. buildHealth теперь использует fresh данные из build(),
// не зависит от кэша overview.

class Overview
{
    private const CACHE_FILE = __DIR__ . '/overview_cache.json';
    private const HEALTH_FILE = __DIR__ . '/health_cache.json';
    private const CACHE_TTL_MS = 60000; // не дёргаем панели чаще раза в минуту
    private const LTV_CACHE_TTL_MS = 120000; // LTV агрегатика — 2 минуты (тяжёлый скан БД)
    private const CACHE_V = 10;          // +predicted_monthly_revenue в overview (было 9)
    private const SNAPSHOT_EVERY_MS = 300000; // пишем снапшот не чаще раза в 5 минут
    private const ONLINE_WINDOW_MS = 300000;  // lastOnline свежее 5 минут = в сети
    private const HISTORY_BUCKET = 180;       // бакеты 120/300 панель отвергает (проверено живьём)
    private const DAY_MS = 86400000;

    /** Точка входа: готовый массив для JSON (с кэшем 60 сек; fresh — мимо кэша). */
    public static function getOverview(bool $fresh = false): array
    {
        if (!$fresh && ($cached = self::readCached()) !== null) {
            $cached['cached'] = true;
            return $cached;
        }
        try {
            return self::rebuild($fresh);
        } catch (\Throwable $e) {
            return ['status' => 'error', 'cached' => false, 'error' => $e->getMessage(), 'servers' => [], 'totals' => []];
        }
    }

    /** Кэш для мгновенной отдачи (даже протухший — для фонового обновления). */
    public static function getCached(): ?array
    {
        $cached = self::readCacheRaw();
        if (!\is_array($cached) || !isset($cached['data'])) return null;
        if (($cached['v'] ?? 0) !== self::CACHE_V) return null;
        return $cached['data'];
    }

    /** Нужен ли фоновый пересчёт: кэша нет или он старше минуты. */
    public static function shouldRefreshBackground(?array $hit): bool
    {
        if (!\is_array($hit)) return true;
        $atMs = (int) ($hit['at_ms'] ?? 0);
        if ($atMs <= 0) return true;
        return ((int) (microtime(true) * 1000) - $atMs) > self::CACHE_TTL_MS;
    }

    /** Свежие данные для health. Использует overview cache (60сек), иначе пересобирает. */
    public static function getHealth(bool $fresh = false): array
    {
        if (!$fresh && is_file(self::HEALTH_FILE)) {
            $hit = json_decode((string) @file_get_contents(self::HEALTH_FILE), true);
            if (\is_array($hit) && isset($hit['data']) && isset($hit['at_ms'])) {
                $nowMs = (int) (microtime(true) * 1000);
                if (($nowMs - (int) $hit['at_ms']) < self::HEALTH_TTL_MS) {
                    $hit['data']['cached'] = true;
                    return $hit['data'];
                }
            }
        }
        // Проверяем: может свежий overview-кеш есть — health из него
        if (!$fresh && ($ovCached = self::readCached()) !== null) {
            $data = self::extractHealthFromOverview($ovCached);
            $data['cached'] = true;
            return $data;
        }
        $nowMs = (int) (microtime(true) * 1000);
        try {
            $data = self::buildHealth();
        } catch (\Throwable $e) {
            $data = ['at_ms' => $nowMs, 'cached' => false, 'ok' => false, 'error' => $e->getMessage()];
        }
        $data['cached'] = false;
        $tmp = self::HEALTH_FILE . '.tmp';
        if (@file_put_contents($tmp, json_encode([
            'at_ms' => $data['at_ms'],
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE)) !== false) {
            @rename($tmp, self::HEALTH_FILE);
        }
        return $data;
    }

    private const HEALTH_TTL_MS = 300000;
    private const VAR_PREFIX = 'ov_';
    private const VAR_CACHE_TTL_MS = 5000;

    private static function readVar(string $key, int $ttlMs = self::VAR_CACHE_TTL_MS)
    {
        $file = sys_get_temp_dir() . '/' . self::VAR_PREFIX . md5($key) . '.json';
        if (!@is_file($file)) return null;
        $raw = @file_get_contents($file);
        if ($raw === false) return null;
        $d = json_decode($raw, true);
        if (!\is_array($d)) return null;
        if ((int) (microtime(true) * 1000) - (int) ($d['ts'] ?? 0) > $ttlMs) {
            @unlink($file);
            return null;
        }
        return $d['data'] ?? null;
    }
    private static function writeVar(string $key, $data, int $ttlSec = 5): void
    {
        $file = sys_get_temp_dir() . '/' . self::VAR_PREFIX . md5($key) . '.json';
        @file_put_contents($file, json_encode(['ts' => (int) (microtime(true) * 1000), 'data' => $data, 'ttl' => $ttlSec * 1000], JSON_UNESCAPED_UNICODE) ?: '');
    }

    /** Извлекает health-поля из обзора (когда кеш overview свежий). */
    private static function extractHealthFromOverview(array $ov): array
    {
        return [
            'status' => 'ok',
            'at_ms' => (int) ($ov['at_ms'] ?? 0),
            'ok' => (bool) ($ov['ok'] ?? false),
            'panel_clients' => (int) ($ov['panel_clients'] ?? 0),
            'panel_enabled' => (int) ($ov['panel_enabled'] ?? 0),
            'panel_disabled' => (int) ($ov['panel_disabled'] ?? 0),
            'panel_online' => (int) ($ov['panel_online'] ?? 0),
            'panel_offline' => (int) ($ov['panel_offline'] ?? 0),
            'panel_active' => (int) ($ov['panel_active'] ?? 0),
            'panel_exhausted' => (int) ($ov['panel_exhausted'] ?? 0),
            'panel_near_expiry' => (int) ($ov['panel_near_expiry'] ?? 0),
            'panel_by_server' => $ov['panel_by_server'] ?? [],
            'panel_clients_list' => $ov['panel_clients_list'] ?? [],
            'db_rows' => (int) ($ov['db_rows'] ?? 0),
            'active_db_rows' => (int) ($ov['active_db_rows'] ?? 0),
            'missing' => $ov['missing'] ?? [],
            'missing_count' => (int) ($ov['missing_count'] ?? 0),
            'ghosts' => $ov['ghosts'] ?? [],
            'ghosts_count' => (int) ($ov['ghosts_count'] ?? 0),
            'subscriptions' => $ov['subscriptions'] ?? [],
            'trials_count' => (int) ($ov['trials_count'] ?? 0),
            'turn_speed_show' => (bool) ($ov['turn_speed_show'] ?? OverviewConfig::isSet('turn_speed_show')),
        ];
    }

    /** Свежие сборка overview (никогда не использует кэш, всегда собирает с панелей). */
    public static function getFresh(): array
    {
        return self::rebuild(true);
    }

    /** Полная сборка + запись кэша (атомарно через rename). */
    public static function rebuild(bool $force = false): array
    {
        $nowMs = (int) (microtime(true) * 1000);
        $data = self::build($nowMs, $force);
        $data['cached'] = false;
        $tmp = self::CACHE_FILE . '.tmp';
        if (@file_put_contents($tmp, json_encode([
            'v' => self::CACHE_V, 'at_ms' => $nowMs, 'data' => $data,
        ], JSON_UNESCAPED_UNICODE)) !== false) {
            @rename($tmp, self::CACHE_FILE);
        }
        self::clearHealthCache();
        return $data;
    }

    public static function clearHealthCache(): void
    {
        @unlink(self::HEALTH_FILE);
    }
    public static function clearCache(): void
    {
        @unlink(self::CACHE_FILE);
    }

    /**
     * Полная сборка: панели + БД + снапшоты + health.
     * @return array{
     *   status:string, at_ms:int, stale:bool, collecting:bool,
     *   servers:array, totals:array, traffic:array, users:array,
     *   panel_clients:int, panel_enabled:int, panel_disabled:int,
     *   panel_online:int, panel_offline:int, panel_active:int,
     *   panel_exhausted:int, panel_near_expiry:int,
     *   panel_by_server:array, panel_clients_list:array,
     *   db_rows:int, active_db_rows:int, missing:array, missing_count:int,
     *   ghosts:array, ghosts_count:int, ok:bool, subscriptions:array
     * }
     */
     private static function trialsCount(): int
     {
        $r = Database::send(
            'SELECT COUNT(*) c FROM qwees_subscriptions WHERE status = ? AND (subscription LIKE ? OR subscription LIKE ?)',
            ['on', 'trial%', 'free%']
        );
        return (int) ($r[0]['c'] ?? 0);
     }

     private static function build(int $nowMs, bool $force = false): array
     {
        $servers = self::serverDataCached($nowMs, $force);
        $fresh = array_filter($servers, fn($s) => ($s['stale'] ?? true) === false);
        $sum = fn(string $k) => array_sum(array_map(fn($s) => (int) ($s[$k] ?? 0), $fresh));
        $totals = [
            'online_now' => $sum('online_now'),
            'clients' => $sum('clients'),
            'up_total' => $sum('up_total'),
            'down_total' => $sum('down_total'),
            'trials_count' => self::trialsCount(),
        ];
        $periods = ['24h' => self::DAY_MS, '7d' => 7 * self::DAY_MS, '30d' => 30 * self::DAY_MS];
        $midnight = (new DateTime('today', new DateTimeZone('Europe/Moscow')))->getTimestamp() * 1000;
        $histories = [];
        foreach ($fresh as $s) {
            $histories[(string) $s['code']] = self::historyWindow((string) ($s['code'] ?? ''), $nowMs, $midnight);
        }
        $traffic = [];
        $collecting = false;
        foreach ($periods as $name => $span) {
            $up = 0;
            $down = 0;
            $ok = false;
            foreach ($fresh as $s) {
                $base = ($histories[(string) ($s['code'] ?? '')]['cuts'] ?? [])[$span] ?? null;
                if ($base === null) {
                    $collecting = true;
                    continue;
                }
                $ok = true;
                $up += max(0, (int) ($s['up_total'] ?? 0) - (int) ($base['up_bytes'] ?? 0));
                $down += max(0, (int) ($s['down_total'] ?? 0) - (int) ($base['down_bytes'] ?? 0));
            }
            $traffic[$name] = $ok ? ['up' => $up, 'down' => $down, 'total' => $up + $down] : null;
        }
        $peak = 0;
        $peakOk = false;
        $onlineAvg = 0;
        $avgOk = false;
        foreach ($fresh as $s) {
            $h = $histories[(string) ($s['code'] ?? '')] ?? [];
            $p = $h['peak'] ?? null;
            if ($p !== null) {
                $peakOk = true;
                $peak = max($peak, (int) $p);
            }
            $a = $h['avg'] ?? null;
            if ($a !== null) {
                $avgOk = true;
                $onlineAvg += (int) $a;
            }
        }
        $totals['online_avg'] = $avgOk ? $onlineAvg : null;
        $users = self::userStats();

        // === Health: сверка БД↔панель ===
        $health = self::buildHealthData($servers, $fresh, $nowMs);

        return [
            'status' => 'ok',
            'at_ms' => $nowMs,
            'stale' => $fresh === [],
            'collecting' => $collecting,
            'servers' => $servers,
            'totals' => $totals + ['peak_today' => $peakOk ? $peak : null],
            'traffic' => $traffic,
            'users' => $users,
            // Поля health прямо в overview (клиент читает из одного JSON)
            'ok' => $health['ok'],
            'panel_clients' => $health['panel_clients'],
            'panel_enabled' => $health['panel_enabled'],
            'panel_disabled' => $health['panel_disabled'],
            'panel_online' => $health['panel_online'],
            'panel_offline' => $health['panel_offline'],
            'panel_active' => $health['panel_active'],
            'panel_exhausted' => $health['panel_exhausted'],
            'panel_near_expiry' => $health['panel_near_expiry'],
            'panel_by_server' => $health['panel_by_server'],
            'panel_clients_list' => $health['panel_clients_list'],
            'db_rows' => $health['db_rows'],
            'active_db_rows' => $health['active_db_rows'],
            'missing' => $health['missing'],
            'missing_count' => $health['missing_count'],
            'ghosts' => $health['ghosts'],
             'ghosts_count' => $health['ghosts_count'],
             'subscriptions' => $health['subscriptions'],
             'trials_count' => $health['trials_count'],
             'predicted_monthly_revenue' => self::predictedRevenue(),
             'turn_speed_show' => OverviewConfig::isSet('turn_speed_show'),
         ];
    }

    /** Данные одного сервера: панель + дельты + пик. */
    private static function serverData(string $code, int $nowMs): array
    {
        ServerNetwork::selectServer(null, $code);
        $srv = ServerNetwork::getServer();
        $base = [
            'code' => $code,
            'country' => (string) ($srv['country'] ?? $code),
            'stale' => true,
        ];
    $batch = Xray::panelBatch(array_merge(
            ['/panel/api/server/status', '/panel/api/inbounds/list', '/panel/api/clients/list', '/panel/api/server/history/cpu/' . self::HISTORY_BUCKET, '/panel/api/server/history/mem/' . self::HISTORY_BUCKET],
            (OverviewConfig::isSet('turn_speed_show') ? ['/panel/api/server/history/netUp/' . self::HISTORY_BUCKET, '/panel/api/server/history/netDown/' . self::HISTORY_BUCKET] : []),
            ['/panel/api/server/history/online/' . self::HISTORY_BUCKET]
        ));
        $status = self::batchObj($batch['/panel/api/server/status'] ?? false);
        $inbounds = self::batchObj($batch['/panel/api/inbounds/list'] ?? false);
        $panelClients = self::batchObj($batch['/panel/api/clients/list'] ?? false);
        if ($status === false || $inbounds === false) return $base;
        $panelGroupMap = [];
        $panelExpiryMap = [];
        foreach ((array) $panelClients as $pc) {
            $email = (string) ($pc['email'] ?? '');
            $subId = (string) ($pc['subId'] ?? '');
            if ($email !== '') $panelGroupMap[$email] = (string) ($pc['group'] ?? '');
            if ($subId !== '') $panelGroupMap[$subId] = (string) ($pc['group'] ?? '');
            if ($subId !== '') $panelExpiryMap[$subId] = (int) ($pc['expiryTime'] ?? 0);
            if ($email !== '') $panelExpiryMap[$email] = (int) ($pc['expiryTime'] ?? 0);
        }
        $up = 0;
        $down = 0;
        $clients = [];
        $inboundTags = [];
        foreach ($inbounds as $in) {
            $tag = (string) ($in['tag'] ?? '');
            $inId = (int) ($in['id'] ?? 0);
            if ($tag !== '' && $inId > 0) $inboundTags[$inId] = $tag;
            $up += (int) ($in['up'] ?? 0);
            $down += (int) ($in['down'] ?? 0);
            foreach ((array) ($in['clientStats'] ?? []) as $c) {
                $totalGB = self::clientTotalGB($c);
                $inboundId = (int) ($c['inboundId'] ?? 0);
                $clients[] = [
                    'email' => (string) ($c['email'] ?? ''),
                    'subId' => (string) ($c['subId'] ?? ''),
                    'up' => (int) ($c['up'] ?? 0),
                    'down' => (int) ($c['down'] ?? 0),
                    'totalGB' => $totalGB,
                    'enable' => (bool) ($c['enable'] ?? false),
                    'online' => ($nowMs - (int) ($c['lastOnline'] ?? 0)) < self::ONLINE_WINDOW_MS,
                    'lastOnline' => (int) ($c['lastOnline'] ?? 0),
                    'inbound' => $inboundTags[$inboundId] ?? '',
                    'inboundId' => $inboundId,
                    'panel_expiry' => (int) ($panelExpiryMap[$c['subId']] ?? ($panelExpiryMap[$c['email']] ?? ((int) ($c['expiryTime'] ?? 0)))),
                ];
            }
        }
        $onlineNow = \count(array_filter($clients, fn($c) => $c['online'] && $c['enable']));
        // Count how many distinct inbounds each client (by subId/email) is bound to
        $inboundCounts = [];
        foreach ($clients as $c) {
            $key = ($c['subId'] ?? '') !== '' ? $c['subId'] : ($c['email'] ?? '');
            if ($key === '') continue;
            $inboundCounts[$key] = ($inboundCounts[$key] ?? 0) + 1;
        }
        self::writeSnapshot($code, $up, $down, $onlineNow, $nowMs);
        usort($clients, fn($a, $b) => $b['down'] <=> $a['down']);
        $mem = (array) ($status['mem'] ?? []);
        $disk = (array) ($status['disk'] ?? []);
        $swap = (array) ($status['swap'] ?? []);
        $xray = (array) ($status['xray'] ?? []);
        $netTraffic = (array) ($status['netTraffic'] ?? []);
        $appStats = (array) ($status['appStats'] ?? []);
        $publicIP = (array) ($status['publicIP'] ?? []);
        return array_merge($base, [
            'stale' => false,
            'online_now' => $onlineNow,
            'clients' => \count($clients),
            'up_total' => $up,
            'down_total' => $down,
            'up_total_h' => self::formatBytes($up),
            'down_total_h' => self::formatBytes($down),
            'cpu' => round((float) ($status['cpu'] ?? 0), 1),
            'cpu_sub' => trim((int) ($status['cpuCores'] ?? 0) . ' Core / ' . (int) ($status['logicalPro'] ?? 0) . 'T · ' . round((float) ($status['cpuSpeedMhz'] ?? 0) / 1000, 2) . ' GHz', ' /·'),
            'mem_used' => (int) ($mem['current'] ?? 0),
            'mem_total' => (int) ($mem['total'] ?? 0),
            'mem_h' => self::formatBytes((int) ($mem['current'] ?? 0)) . ' / ' . self::formatBytes((int) ($mem['total'] ?? 0)),
            'swap_used' => (int) ($swap['current'] ?? 0),
            'swap_total' => (int) ($swap['total'] ?? 0),
            'swap_h' => self::formatBytes((int) ($swap['current'] ?? 0)) . ' / ' . self::formatBytes((int) ($swap['total'] ?? 0)),
            'disk_used' => (int) ($disk['current'] ?? 0),
            'disk_total' => (int) ($disk['total'] ?? 0),
            'disk_h' => self::formatBytes((int) ($disk['current'] ?? 0)) . ' / ' . self::formatBytes((int) ($disk['total'] ?? 0)),
            'disk_free_h' => self::formatBytes(max(0, (int) ($disk['total'] ?? 0) - (int) ($disk['current'] ?? 0))),
            'uptime_s' => (int) ($status['uptime'] ?? 0),
            'uptime_h' => self::formatUptime((int) ($status['uptime'] ?? 0)),
            'xray_state' => (string) ($xray['state'] ?? 'unknown'),
            'xray_version' => (string) ($xray['version'] ?? ''),
            'tcp' => (int) ($status['tcpCount'] ?? 0),
            'udp' => (int) ($status['udpCount'] ?? 0),
            'net_sent' => (int) ($netTraffic['sent'] ?? 0),
            'net_recv' => (int) ($netTraffic['recv'] ?? 0),
            'net_sent_h' => self::formatBytes((int) ($netTraffic['sent'] ?? 0)),
            'net_recv_h' => self::formatBytes((int) ($netTraffic['recv'] ?? 0)),
            'panel_mem_h' => self::formatBytes((int) ($appStats['mem'] ?? 0)),
            'panel_threads' => (int) ($appStats['threads'] ?? 0),
            'panel_uptime_h' => self::formatUptime((int) ($appStats['uptime'] ?? 0)),
            'ip' => (string) ($publicIP['ipv4'] ?? ''),
            'history' => self::serverHistory($batch),
            'turn_speed_show' => OverviewConfig::isSet('turn_speed_show'),
            'clients_list' => array_map(fn($c) => [
                'subId' => $c['subId'], 'email' => $c['email'],
                'enable' => $c['enable'], 'up' => $c['up'], 'down' => $c['down'],
                'up_h' => self::formatSpeed($c['up']),
                'down_h' => self::formatSpeed($c['down']),
                'totalGB' => $c['totalGB'], 'lastOnline' => $c['lastOnline'],
                'online' => $c['online'],
                'inbound' => $c['inbound'] ?? '',
                'panel_group' => $panelGroupMap[$c['subId']] ?? ($panelGroupMap[$c['email']] ?? ''),
                'panel_expiry' => (int) ($c['panel_expiry'] ?? 0),
            ], $clients),
            'top' => \array_slice(array_map(fn($c) => $c + ['down_h' => self::formatBytes($c['down'])], $clients), 0, 5),
        ]);
    }

    /** totalGB из clientStats (иногда строка, иногда число). */
    private static function clientTotalGB(array $c): int
    {
        if (isset($c['totalGB'])) {
            $v = $c['totalGB'];
            if (\is_int($v)) return $v;
            if (\is_string($v) && ctype_digit($v)) return (int) $v;
            if (\is_numeric($v)) return (int) $v;
        }
        return 0;
    }

    /** Таймсерии ~3 ч для спарклайнов (по одной точке в 3 мин). Данные уже приехали батчем. */
    private static function serverHistory(array $batch): array
    {
        $out = [];
        foreach (['cpu' => 'cpu', 'mem' => 'mem', 'netUp' => 'netUp', 'netDown' => 'netDown', 'online' => 'online'] as $k => $m) {
            $pts = self::batchObj($batch['/panel/api/server/history/' . $m . '/' . self::HISTORY_BUCKET] ?? false);
            $out[$k] = $pts === false ? [] : array_map(fn($p) => [(int) ($p['t'] ?? 0), (float) ($p['v'] ?? 0)], $pts);
        }
        return $out;
    }

    /** Распаковка {success,obj} из батча. */
    private static function batchObj(array|false $res): array|false
    {
        if (!\is_array($res) || ($res['success'] ?? false) !== true || !\is_array($res['obj'] ?? null)) return false;
        return $res['obj'];
    }

    /** Агрегаты пользователей из БД (один запрос). */
    private static function userStats(): array
    {
        $todayRange = (new DateTime('today', new DateTimeZone('Europe/Moscow')))->format('Y-m-d');
        $row = Database::send(
            'SELECT (SELECT COUNT(*) FROM qwees_users) total, (SELECT COUNT(*) FROM qwees_subscriptions WHERE status = \'on\') active, (SELECT COUNT(*) FROM qwees_users WHERE DATE(created_at) = ?) today',
            [$todayRange]
        );
        $row = is_array($row) && isset($row[0]) ? $row[0] : [];
        return [
            'total' => (int) (($row['total'] ?? 0)),
            'active' => (int) (($row['active'] ?? 0)),
            'new_today' => (int) (($row['today'] ?? 0)),
        ];
    }

    // ==================== Снапшоты ====================

    /** Пишем снапшот не чаще раза в 5 минут (троттл — локальный файл, без запроса в БД). */
    private static function writeSnapshot(string $server, int $up, int $down, int $online, int $nowMs): void
    {
        $flag = sys_get_temp_dir() . '/qwees_snap_' . preg_replace('/[^a-z0-9]/i', '', $server);
        $last = is_file($flag) ? (int) @file_get_contents($flag) : 0;
        if (($nowMs - $last) < self::SNAPSHOT_EVERY_MS) return;
        Database::send(
            'INSERT INTO qwees_traffic_snapshots (server, up_bytes, down_bytes, online_now, at_ms) VALUES (?, ?, ?, ?, ?)',
            [$server, $up, $down, $online, $nowMs]
        );
        @file_put_contents($flag, (string) $nowMs);
        if (random_int(1, 20) === 1) {
            Database::send('DELETE FROM qwees_traffic_snapshots WHERE at_ms < ?', [$nowMs - 35 * self::DAY_MS]);
        }
    }

    /**
     * История сервера: срезы 24ч/7д/30д тремя простыми запросами + пик сегодня.
     * Без UNION: голые UNION-леги с LIMIT не принимает MySQL, скобочные — SQLite.
     * @return array{cuts:array<int,array>,peak:?int}
     */
    private static function historyWindow(string $server, int $nowMs, int $midnight): array
    {
        $cuts = [];
        $map = [self::DAY_MS => 'd1', 7 * self::DAY_MS => 'd7', 30 * self::DAY_MS => 'd30'];
        $revMap = array_flip($map);
        foreach ($map as $offsetMs => $label) {
            $stmt = Database::send(
                'SELECT up_bytes, down_bytes, ? AS lbl FROM qwees_traffic_snapshots WHERE server = ? AND at_ms <= ? AND at_ms = (SELECT MAX(at_ms) FROM qwees_traffic_snapshots WHERE server = ? AND at_ms <= ?) LIMIT 1',
                [(string) $label, $server, $nowMs - $offsetMs, $server, $nowMs - $offsetMs]
            );
            if (\is_array($stmt) && isset($stmt[0]['lbl']) && isset($revMap[(string) $stmt[0]['lbl']])) {
                $cuts[$revMap[(string) $stmt[0]['lbl']]] = $stmt[0];
            }
        }
        $peak = Database::send('SELECT MAX(online_now) m FROM qwees_traffic_snapshots WHERE server = ? AND at_ms >= ?', [$server, $midnight]);
        $avg = Database::send('SELECT AVG(online_now) m FROM qwees_traffic_snapshots WHERE server = ? AND at_ms >= ?', [$server, $midnight]);
        return ['cuts' => $cuts, 'peak' => isset($peak[0]['m']) && $peak[0]['m'] !== null ? (int) $peak[0]['m'] : null, 'avg' => isset($avg[0]['m']) && $avg[0]['m'] !== null ? (int) $avg[0]['m'] : null];
    }

    // ==================== Health (сверка БД↔панель) ====================

    /**
     * Свёрка БД и панелей. $servers — уже собранные данные (fresh).
     * @return array{ok:bool,panel_clients:int,panel_enabled:int,panel_disabled:int,panel_online:int,panel_offline:int,panel_active:int,panel_exhausted:int,panel_near_expiry:int,panel_by_server:array,panel_clients_list:array,db_rows:int,active_db_rows:int,missing:array,missing_count:int,ghosts:array,ghosts_count:int,subscriptions:array}
     */
    private static function buildHealthData(array $servers, array $fresh, int $nowMs): array
    {
        $panel = [];//ключ (subId, иначе email) => клиент с данными
        $panelByServer = [];//server code => ['total','on','off']
        // Считаем количество inbounds на клиента по всем серверам
        $inboundCounts = [];
        foreach ($servers as $s) {
            if (($s['stale'] ?? true)) continue;
            foreach ((array) ($s['clients_list'] ?? []) as $c) {
                $key = ($c['subId'] ?? '') !== '' ? $c['subId'] : ($c['email'] ?? '');
                if ($key === '') continue;
                $inboundCounts[$key] = ($inboundCounts[$key] ?? 0) + 1;
            }
        }
        foreach ($servers as $s) {
            if (($s['stale'] ?? true)) continue;
            $code = (string) ($s['code'] ?? '');
            $panelByServer[$code] = ['total' => 0, 'on' => 0, 'off' => 0];
            foreach ((array) ($s['clients_list'] ?? []) as $c) {
                $key = ($c['subId'] ?? '') !== '' ? $c['subId'] : ($c['email'] ?? '');
                if ($key === '') continue;
                $enable = (bool) ($c['enable'] ?? false);
                $online = (bool) ($c['online'] ?? false);
                $panel[$key] = [
                    'server' => $code,
                    'country' => (string) ($s['country'] ?? ''),
                    'email' => (string) ($c['email'] ?? ''),
                    'subId' => (string) ($c['subId'] ?? ''),
                    'enable' => $enable,
                    'online' => $online,
                    'up' => (int) ($c['up'] ?? 0),
                    'down' => (int) ($c['down'] ?? 0),
                    'up_h' => (string) ($c['up_h'] ?? self::formatSpeed((int) ($c['up'] ?? 0))),
                    'down_h' => (string) ($c['down_h'] ?? self::formatSpeed((int) ($c['down'] ?? 0))),
                    'totalGB' => (int) ($c['totalGB'] ?? 0),
                    'inbound' => (string) ($c['inbound'] ?? ''),
                    'inbounds' => (int) ($inboundCounts[$key] ?? 1),
                     'panel_group' => (string) ($c['panel_group'] ?? ''),
                     'panel_expiry' => (int) ($c['panel_expiry'] ?? 0),
                     'expiry' => (int) ($c['panel_expiry'] ?? 0),
                 ];
                $panelByServer[$code]['total']++;
                if ($enable && $online) $panelByServer[$code]['on']++;
                elseif ($enable && !$online) $panelByServer[$code]['off']++;
            }
        }
        $rows = Database::send('SELECT s.uniID, u.email, s.status, s.subscription, s.expiry, s.created_at FROM qwees_subscriptions s LEFT JOIN qwees_users u ON u.uniID = s.uniID');
        $rows = \is_array($rows) ? $rows : [];
        $byUni = [];
        $activeDbCount = 0;
        $trialsCount = 0;
        foreach ($rows as $r) {
            $uni = (string) ($r['uniID'] ?? '');
            if ($uni !== '') $byUni[$uni] = $r;
            $active = (($r['status'] ?? '') === 'on') && ((int) ($r['expiry'] ?? 0) > $nowMs);
            if ($active) $activeDbCount++;
            if ($active && preg_match('/trial|free/i', (string) ($r['subscription'] ?? ''))) $trialsCount++;
        }
        // Синхронизируем expiry из панели в БД (односторонняя: панель → БД).
        // Делает счёт точным: больше "оплачено, но ключа нет" из-за рассинхронизации.
        self::syncPanelExpiryToDb($panel, $byUni, $rows);
        // Привязываем expiry из БД к клиентам панели (только как fallback, если панель не вернула expiryTime)
        foreach ($panel as $key => $p) {
            $r = $byUni[$key] ?? null;
            if ($r === null) {
                foreach ($rows as $rr) {
                    if ((string) ($rr['email'] ?? '') === $key) {
                        $r = $rr;
                        break;
                    }
                }
            }
            if ($r !== null) {
                if (empty($p['expiry']) && !empty($r['expiry'])) {
                    $panel[$key]['expiry'] = (int) ($r['expiry'] ?? 0);
                }
                $panel[$key]['created_at'] = (string) ($r['created_at'] ?? '');
            }
            if (!empty($panel[$key]['panel_group'])) {
                $panel[$key]['group'] = $panel[$key]['panel_group'];
            } elseif ($r !== null) {
                $panel[$key]['group'] = self::planName((string) ($r['subscription'] ?? ''));
            }
        }
        $missing = [];//оплачено в БД, а ключа в панели нет
        foreach ($rows as $r) {
            $uni = (string) ($r['uniID'] ?? '');
            if ($uni === '') continue;
            $active = (($r['status'] ?? '') === 'on') && ((int) ($r['expiry'] ?? 0) > $nowMs);
            if (!$active) continue;
            if (!isset($panel[$uni]) && !isset($panel[(string) ($r['email'] ?? '')])) {
                $missing[] = ['uniID' => $uni, 'email' => (string) ($r['email'] ?? '')];
            }
        }
        $ghosts = [];//ключ в панели, а активной подписки в БД нет
        foreach ($panel as $key => $p) {
            $r = $byUni[$key] ?? null;
            if ($r === null) {
                foreach ($rows as $rr) {
                    if ((string) ($rr['email'] ?? '') === $key) {
                        $r = $rr;
                        break;
                    }
                }
            }
            $active = $r !== null && (($r['status'] ?? '') === 'on') && ((int) ($r['expiry'] ?? 0) > $nowMs);
            if (!$active) $ghosts[] = ['key' => $key, 'email' => $p['email'], 'server' => $p['server'], 'enable' => $p['enable']];
        }
        $subs = self::checkSubscriptionsBulkCached($fresh);
        $panelTotal = 0;
        $panelOnline = 0;
        $panelOffline = 0;
        $panelDisabled = 0;
        $panelActive = 0;
        $panelExhausted = 0;
        $panelNearExpiry = 0;
        foreach ($panel as $p) {
            $panelTotal++;
            if ($p['enable']) {
                if ($p['online']) $panelOnline++;
                else $panelOffline++;
                $panelActive++;
                $used = (int) ($p['up'] ?? 0) + (int) ($p['down'] ?? 0);
                $total = (int) ($p['totalGB'] ?? 0);
                if ($total > 0 && $used >= $total) $panelExhausted++;
                elseif ($total > 0 && ($total - $used) / $total < 0.1) $panelNearExpiry++;
            } else {
                $panelDisabled++;
            }
        }
        $ok = ($missing === [] && $ghosts === []) && array_reduce($subs, fn($a, $x) => $a && ($x['ok'] ?? false), true);
        $panelList = [];
        foreach ($panel as $key => $p) {
            $p['expiry_days'] = self::formatDuration((int) ($p['expiry'] ?? 0), $nowMs);
            $secs = (int) (($p['expiry'] ?? 0) / 1000 - $nowMs / 1000);
            $p['expiry_days_num'] = $p['expiry'] > 0 ? (int) floor($secs / 86400) : PHP_INT_MAX;
            $panelList[] = $p;
        }
        usort($panelList, fn($a, $b) => ($a['created_at'] ?? '') <=> ($b['created_at'] ?? ''));
        return [
            'ok' => $ok,
            'panel_clients' => $panelTotal,
            'panel_enabled' => $panelTotal - $panelDisabled,
            'panel_disabled' => $panelDisabled,
            'panel_online' => $panelOnline,
            'panel_offline' => $panelOffline,
            'panel_active' => $panelActive,
            'panel_exhausted' => $panelExhausted,
            'panel_near_expiry' => $panelNearExpiry,
            'panel_by_server' => $panelByServer,
            'panel_clients_list' => $panelList,
            'db_rows' => \count($rows),
            'active_db_rows' => $activeDbCount,
            'missing' => \array_slice($missing, 0, 50),
            'missing_count' => \count($missing),
            'ghosts' => \array_slice($ghosts, 0, 50),
            'ghosts_count' => \count($ghosts),
            'subscriptions' => $subs,
            'trials_count' => $trialsCount,
        ];
    }

    /**
     * Синхронизация expiry из панели в БД.
     * Односторонняя: панель (источник правды о времени) → БД.
     * Только для клиентов с panel_expiry > 0 и статусом 'on' в БД.
     */
    private static function syncPanelExpiryToDb(array $panel, array $byUni, array $rows): void
    {
        if (empty($panel)) return;
        $byEmail = [];
        foreach ($rows as $r) {
            $email = (string) ($r['email'] ?? '');
            if ($email !== '') $byEmail[$email] = $r;
        }
        $updates = [];
        foreach ($panel as $key => $p) {
            $panelExpiry = (int) ($p['panel_expiry'] ?? 0);
            if ($panelExpiry <= 0) continue;
            $r = $byUni[$key] ?? null;
            if ($r === null) $r = $byEmail[$key] ?? null;
            if ($r === null) continue;
            $uni = (string) ($r['uniID'] ?? '');
            if ($uni === '') continue;
            $dbStatus = (string) ($r['status'] ?? '');
            if ($dbStatus !== 'on') continue;
            $dbExpiry = (int) ($r['expiry'] ?? 0);
            if ($dbExpiry === $panelExpiry) continue;
            $updates[$uni] = $panelExpiry;
        }
        if (empty($updates)) return;
        $cases = '';
        $params = [];
        foreach ($updates as $uni => $exp) {
            $cases .= ' WHEN ? THEN ?';
            $params[] = $uni;
            $params[] = $exp;
        }
        $placeholders = implode(',', array_fill(0, count($updates), '?'));
        $params = array_merge($params, array_keys($updates));
        Database::send(
            "UPDATE qwees_subscriptions SET expiry = CASE uniID " . $cases . " END WHERE uniID IN ($placeholders)",
            $params
        );
    }

    /**
     * Массовая проверка подписей всех серверов параллельно (curl_multi).
     * Один round-trip вместо N последовательных curl — экономит до (N-1)*timeout.
     */
    private const SUB_CACHE_TTL_MS = 30000;

    private static function checkSubscriptionsBulkCached(array $fresh): array
    {
        $codes = [];
        foreach ($fresh as $s) {
            $codes[] = (string) ($s['code'] ?? '');
        }
        $sig = md5(implode('|', $codes));
        $cached = self::readVar('checksub_' . $sig, self::SUB_CACHE_TTL_MS);
        if (is_array($cached)) return $cached;
        $res = self::checkSubscriptionsBulk($fresh);
        self::writeVar('checksub_' . $sig, $res, 30);
        return $res;
    }

    private static function checkSubscriptionsBulk(array $fresh): array
    {
        $urls = [];
        foreach ($fresh as $s) {
            $code = (string) ($s['code'] ?? '');
            $clients = (array) ($s['clients_list'] ?? []);
            $subId = '';
            foreach ($clients as $c) {
                if (($c['enable'] ?? false) && ($c['subId'] ?? '') !== '') {
                    $subId = $c['subId'];
                    break;
                }
            }
            if ($subId === '') {
                $urls[$code] = null;
                continue;
            }
            ServerNetwork::selectServer(null, $code);
            $srv = ServerNetwork::getServer();
            $url = rtrim((string) ($srv['XUI_URL_SUBSCRIPTION'] ?? ''), '/') . '/' . $subId;
            $urls[$code] = ($url === '/' . $subId) ? null : $url;
        }

        $results = [];
        $mh = curl_multi_init();
        $handles = [];
        $map = [];
        foreach ($urls as $code => $url) {
            $base = ['server' => $code, 'ok' => false, 'checks' => [], 'ms' => 0];
            if ($url === null) {
                $results[$code] = $base + ['error' => 'нет клиента или URL подписки'];
                continue;
            }
            $t = microtime(true);
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_USERAGENT => 'QweesVPN-healthcheck/1.0',
            ]);
            curl_multi_add_handle($mh, $ch);
            $handles[] = $ch;
            $map[(int) $ch] = ['code' => $code, 't' => $t, 'url' => $url];
        }

        $active = null;
        do {
            $status = curl_multi_exec($mh, $active);
            if ($active) {
                curl_multi_select($mh, 0.1);
            }
        } while ($status === CURLM_OK && $active);

        foreach ($handles as $ch) {
            $key = (int) $ch;
            $info = $map[$key];
            $code = $info['code'];
            $ms = (int) round((microtime(true) - $info['t']) * 1000);
            $body = curl_multi_getcontent($ch);
            $codeHttp = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);

            $checks = ['http' => $codeHttp === 200 && $err === '', 'non_empty' => \is_string($body) && strlen((string) $body) > 0];
            $links = [];
            $text = trim((string) ($body ?? ''));
            if ($text !== '' && strpos($text, '://') === false) {
                $dec = base64_decode($text, true);
                if ($dec !== false && strpos($dec, '://') !== false) $text = $dec;
            }
            if ($text !== '') {
                foreach (preg_split('/\s+/', $text) as $ln) {
                    if (str_starts_with($ln, 'vless://')) $links[] = $ln;
                }
            }
            $checks['has_links'] = $links !== [];
            $params = [];
            if ($links !== []) {
                parse_str((string) (parse_url($links[0], PHP_URL_QUERY) ?? ''), $params);
            }
            $checks['reality'] = ($params['security'] ?? '') === 'reality';
            $checks['pbk'] = ($params['pbk'] ?? '') !== '';
            $checks['sni'] = ($params['sni'] ?? '') !== '';
            $base = ['server' => $code, 'ok' => true, 'checks' => $checks, 'ms' => $ms, 'link_count' => \count($links)];
            $base['ok'] = !\in_array(false, $checks, true);
            if (!$base['ok'] && $err !== '') $base['error'] = mb_substr($err, 0, 120);
            $results[$code] = $base;
        }
        curl_multi_close($mh);

        $out = [];
        foreach ((array) $fresh as $s) {
            $code = (string) ($s['code'] ?? '');
            $out[] = $results[$code] ?? ['server' => $code, 'ok' => false, 'checks' => [], 'error' => 'не обработан'];
        }
        return $out;
    }

    /**
     * Health из свежего build (для /api/admin/health?fresh=1).
     * Возвращает тот же массив, что и build(), но только health-поля.
     */
    private static function buildHealth(): array
    {
        $nowMs = (int) (microtime(true) * 1000);
        // Не force: свежие панели делит 5-сек кэш с overview, повторного удара по 3x-ui нет
        $servers = self::serverDataCached($nowMs, false);
        $freshServers = array_filter($servers, fn($s) => ($s['stale'] ?? true) === false);
        $h = self::buildHealthData($servers, $freshServers, $nowMs);
        return [
            'status' => 'ok',
            'at_ms' => $nowMs,
            'ok' => $h['ok'],
            'panel_clients' => $h['panel_clients'],
            'panel_enabled' => $h['panel_enabled'],
            'panel_disabled' => $h['panel_disabled'],
            'panel_online' => $h['panel_online'],
            'panel_offline' => $h['panel_offline'],
            'panel_active' => $h['panel_active'],
            'panel_exhausted' => $h['panel_exhausted'],
            'panel_near_expiry' => $h['panel_near_expiry'],
            'panel_by_server' => $h['panel_by_server'],
            'panel_clients_list' => $h['panel_clients_list'],
            'db_rows' => $h['db_rows'],
            'active_db_rows' => $h['active_db_rows'],
            'trials_count' => $h['trials_count'] ?? 0,
            'missing' => $h['missing'],
            'missing_count' => $h['missing_count'],
            'ghosts' => $h['ghosts'],
            'ghosts_count' => $h['ghosts_count'],
            'subscriptions' => $h['subscriptions'],
            'predicted_monthly_revenue' => self::predictedRevenue(),
            'turn_speed_show' => OverviewConfig::isSet('turn_speed_show'),
        ];
    }
    private static function serverDataCached(int $nowMs, bool $force = false): array
    {
        $bucket = (int) ($nowMs / 5000);
        $key = self::VAR_PREFIX . 'srv_data_' . $bucket;
        if (!$force) {
            $cached = self::readVar($key, self::VAR_CACHE_TTL_MS);
            if (is_array($cached)) return $cached;
        }
        $servers = [];
        foreach (ServerNetwork::getServerCodes() as $code) {
            $servers[] = self::serverData((string) $code, $nowMs);
        }
        // Пишем и при force: параллельный запрос (overview+health) переиспользует свежие панели
        self::writeVar($key, $servers, 5);
        // Чистим соседний бакет, чтобы tmp не копился
        @unlink(sys_get_temp_dir() . '/' . self::VAR_PREFIX . md5(self::VAR_PREFIX . 'srv_data_' . ($bucket - 1)) . '.json');
        return $servers;
    }

    // ==================== Очистка неактивных ====================

    /** Прогноз прибыли / мес из кэша LTV (2 мин), без пересчёта. */
    public static function predictedRevenue(): float
    {
        $cached = self::readVar('ltv_base', self::LTV_CACHE_TTL_MS);
        if (!is_array($cached)) return 0.0;
        return (float) ($cached['metrics']['predicted_monthly_revenue'] ?? 0);
    }

    /** Превью очистки: что будет удалено, без изменений. */
    public static function cleanupPreview(): array
    {
        $nowMs = (int) (microtime(true) * 1000);
        $rows = Database::send("SELECT s.uniID, u.email, s.status, s.expiry FROM qwees_subscriptions s LEFT JOIN qwees_users u ON u.uniID = s.uniID WHERE s.expiry <= ? AND s.status NOT IN ('pending_vpn','banned')", [$nowMs]);
        $rows = \is_array($rows) ? $rows : [];
        $expired = [];
        $off = [];
        foreach ($rows as $r) {
            if (($r['status'] ?? '') === 'on') $expired[] = $r;
            else $off[] = $r;
        }
        $skBanned = Database::send("SELECT COUNT(*) c FROM qwees_subscriptions WHERE status = 'banned'");
        $skPending = Database::send("SELECT COUNT(*) c FROM qwees_subscriptions WHERE status = 'pending_vpn'");
        $health = self::getHealth(true);
        return [
            'status' => 'ok',
            'db_expired' => array_map(fn($r) => ['uniID' => (string) ($r['uniID'] ?? ''), 'email' => (string) ($r['email'] ?? '')], \array_slice($expired, 0, 100)),
            'db_expired_count' => \count($expired),
            'db_off' => array_map(fn($r) => ['uniID' => (string) ($r['uniID'] ?? ''), 'email' => (string) ($r['email'] ?? '')], \array_slice($off, 0, 100)),
            'db_off_count' => \count($off),
            'skipped_banned' => (int) (($skBanned[0]['c'] ?? 0)),
            'skipped_pending' => (int) (($skPending[0]['c'] ?? 0)),
            'panel_ghosts' => $health['ghosts'] ?? [],
            'panel_ghosts_count' => (int) ($health['ghosts_count'] ?? 0),
        ];
    }

    /** Выполнение очистки: пересчитываем всё на сервере, списки от клиента игнорируем. */
    public static function cleanupExecute(): array
    {
        $nowMs = (int) (microtime(true) * 1000);
        $res = ['status' => 'ok', 'db_deleted' => 0, 'panel_deleted' => [], 'panel_failed' => []];
        $rows = Database::send("SELECT uniID FROM qwees_subscriptions WHERE expiry <= ? AND status NOT IN ('pending_vpn','banned')", [$nowMs]);
        $rows = \is_array($rows) ? $rows : [];
        if ($rows !== []) {
            $del = Database::send("DELETE FROM qwees_subscriptions WHERE expiry <= ? AND status NOT IN ('pending_vpn','banned')", [$nowMs]);
            if ($del === false) return ['status' => 'error', 'message' => 'Не удалось очистить базу'];
            $res['db_deleted'] = \count($rows);
        }
        $dead = Database::send("SELECT uniID FROM qwees_subscriptions WHERE expiry <= ? AND status = 'pending_vpn'", [$nowMs]);
        if (\is_array($dead) && $dead !== []) {
            Database::send("DELETE FROM qwees_subscriptions WHERE expiry <= ? AND status = 'pending_vpn'", [$nowMs]);
            $res['db_deleted'] += \count($dead);
            foreach ($dead as $d) {
                $u = (string) ($d['uniID'] ?? '');
                if ($u !== '') \Setting\Route\Function\Controllers\Client\Src\Client::forget($u);
            }
        }
        $health = self::getHealth(true);
        foreach ((array) ($health['ghosts'] ?? []) as $g) {
            $key = (string) ($g['key'] ?? '');
            if ($key === '') continue;
            $del = \Setting\Route\Function\Controllers\Vpn\V2ray\Xray::deleteClientFromPanel($key, (string) ($g['server'] ?? '') !== '' ? (string) $g['server'] : null);
            $st = (string) ($del['status'] ?? '');
            if ($st === 'ok' || $st === 'partial') $res['panel_deleted'][] = $key;
            else $res['panel_failed'][] = ['key' => $key, 'error' => (string) ($del['message'] ?? 'ошибка')];
        }
        self::clearHealthCache();
        @unlink(self::CACHE_FILE);
        (new \Setting\Route\Function\Controllers\Admin\Admin())->LoggerCRM(
            "очистка неактивных: строк БД {$res['db_deleted']}, ключей панели " . \count($res['panel_deleted']) . ", ошибок " . \count($res['panel_failed'])
        );
        return $res;
    }

    // ==================== Кэш ====================

    private static function readCached(): ?array
    {
        return self::readCache();
    }

    private static function readCache(): ?array
    {
        $cached = self::readCacheRaw();
        if (!\is_array($cached) || !isset($cached['data'])) return null;
        if (($cached['v'] ?? 0) !== self::CACHE_V) return null;
        $nowMs = (int) (microtime(true) * 1000);
        if (($nowMs - (int) ($cached['at_ms'] ?? 0)) > self::CACHE_TTL_MS) return null;
        return $cached['data'];
    }

    private static function readCacheRaw(): ?array
    {
        if (!is_file(self::CACHE_FILE)) return null;
        $d = json_decode((string) @file_get_contents(self::CACHE_FILE), true);
        return \is_array($d) ? $d : null;
    }

    // ==================== Формат ====================

    /** Преобразует subscription URL/статус в название тарифа (группы клиента). */
    public static function planName(string $sub): string
    {
        if ($sub === '' || $sub === 'trial') return 'Пробная';
        if ($sub === 'bonus') return 'Бонусная';
        if ($sub === 'free') return 'Пробная';
        if (str_starts_with($sub, 'pending_')) return 'Ожидающая';
        if (str_starts_with($sub, 'http')) return 'Оплаченная';
        return $sub;
    }

    public static function formatDuration(int $expiryMs, int $nowMs): string
    {
        if ($expiryMs <= 0) return 'вечна';
        $seconds = (int) (($expiryMs - $nowMs) / 1000);
        if ($seconds < 0) return 'истекла';
        $days = (int) floor($seconds / 86400);
        $hours = (int) floor(($seconds % 86400) / 3600);
        if ($days > 0) return "через {$days}д";
        if ($hours > 0) return "через {$hours}ч";
        $min = (int) floor(($seconds % 3600) / 60);
        return "через {$min}м";
    }

    public static function formatBytes(int|float $bytes): string
    {
        $bytes = max(0, (float) $bytes);
        if ($bytes < 1024) return (int) $bytes . ' B';
        $units = ['KB', 'MB', 'GB', 'TB', 'PB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, \count($units));
        return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i - 1];
    }

    /** Скорость трафика: всегда в KB+ (B → 0.00 KB), для меток ↑/↓ в таблице клиентов */
    public static function formatSpeed(int|float $bytes): string
    {
        $bytes = max(0, (float) $bytes);
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $i = $bytes < 1024 ? 1 : (int) floor(log($bytes, 1024));
        $i = min($i, \count($units) - 1);
        $val = round($bytes / (1024 ** $i), 2);
        return number_format($val, 2, '.', '') . ' ' . $units[$i];
    }

    public static function formatUptime(int $sec): string
    {
        $sec = max(0, $sec);
        $d = (int) floor($sec / 86400);
        $h = (int) floor(($sec % 86400) / 3600);
        $m = (int) floor(($sec % 3600) / 60);
        return $d > 0 ? "{$d}д {$h}ч" : ($h > 0 ? "{$h}ч {$m}м" : "{$m}м");
    }

    /** LTV analytics: классификация клиентов (постоянные, лидеры) + прогноз прибыли. Кэшируется 2 мин. */
    public static function getLtvAnalytics(bool $fresh = false, ?int $days = null, ?string $month = null): array
    {
        // Кэш per-days/month (TTL 2 мин); fresh только по явному ?fresh=1
        if (!$fresh && !isset($_GET['fresh'])) {
            $key = ($days !== null) ? "ltv_d{$days}" : (($month !== null) ? "ltv_m{$month}" : 'ltv_base');
            $cached = self::readVar($key, self::LTV_CACHE_TTL_MS);
            if ($cached !== null) {
                $cached['cached'] = true;
                return $cached;
            }
        }
        try {
            $data = self::buildLtvAnalytics($days, $month);
        } catch (\Throwable $e) {
            $data = [
                'generated_at' => date('c'),
                'period_label' => $days !== null ? "за $days дней" : ($month !== null ? "месяц $month" : 'все время'),
                'metrics' => [
                    'total_subscriptions' => 0, 'active_subscriptions' => 0,
                    'recurring_clients' => 0, 'leader_clients' => 0,
                    'total_ltv' => 0, 'avg_ltv' => 0,
                    'avg_payments_per_client' => 0,
                    'predicted_monthly_revenue' => 0, 'forecast_min' => 0, 'forecast_max' => 0,
                    'actual_monthly_revenue' => 0, 'active_payments_revenue' => 0,
                ],
                'monthly_revenue' => [],
                'top_clients_by_ltv' => [],
                'recurring_clients' => [],
                'trial_conversions' => [],
                'repeat_purchase_days' => 0,
                'recurring_count' => 0,
                'price_optimization' => [],
                'error' => $e->getMessage(),
            ];
        }
        $data['cached'] = false;
        if (!isset($data['error'])) {
            $key = ($days !== null) ? "ltv_d{$days}" : (($month !== null) ? "ltv_m{$month}" : 'ltv_base');
            self::writeVar($key, $data, 120);
        }
        return $data;
    }

    /** Сборка аналитики LTV из БД */
    private static function buildLtvAnalytics(?int $days = null, ?string $month = null): array
    {
        $nowMs = (int) (microtime(true) * 1000);

        $subscriptions = Database::send("SELECT uniID, status, subscription, amount, count_days, count_devices, expiry, created_at FROM qwees_subscriptions ORDER BY uniID, expiry DESC");
        $subscriptions = \is_array($subscriptions) ? $subscriptions : [];

        $users = Database::send("SELECT uniID, refer_count, created_at FROM qwees_users WHERE uniID IS NOT NULL");

        $dateFrom = null;
        $dateTo = null;
        if ($month !== null) {
            $m = preg_replace('/[^0-9\-]/', '', (string) $month);
            if (preg_match('/^\d{4}-\d{2}$/', $m)) {
                $dateFrom = strtotime($m . '-01');
                $dateTo = strtotime($m . '-' . date('t', strtotime($m . '-01')) . ' 23:59:59');
            }
        } elseif ($days !== null) {
            $dateFrom = strtotime('-' . (int) $days . ' days');
        }

        $payAggAll = Database::send(
            "SELECT uniID, amount, created_at FROM qwees_payments WHERE status = 'succeeded' ORDER BY uniID, created_at ASC"
        );
        $payAggAll = \is_array($payAggAll) ? $payAggAll : [];

        $paymentsByUniDateAll = [];
        $ltvByUser = [];
        $paymentCount = [];
        foreach ($payAggAll as $row) {
            $u = (string) ($row['uniID'] ?? '');
            if ($u === '') continue;
            $ca = (string) ($row['created_at'] ?? '');
            $ts = $ca ? strtotime($ca) : 0;
            $inPeriod = true;
            if ($dateFrom !== null && $ts < $dateFrom) $inPeriod = false;
            if (isset($dateTo) && $ts > $dateTo) $inPeriod = false;
            if ($inPeriod) {
                $ltvByUser[$u] = ($ltvByUser[$u] ?? 0.0) + (float) ($row['amount'] ?? 0);
                $paymentCount[$u] = ($paymentCount[$u] ?? 0) + 1;
            }
            $paymentsByUniDateAll[$u][] = [
                'amount' => (float) ($row['amount'] ?? 0),
                'created_at' => $ca,
                'ts' => $ts,
            ];
        }

        $referralCount = [];
        foreach ($users as $u) {
            $referralCount[(string) ($u['uniID'] ?? '')] = (int) ($u['refer_count'] ?? 0);
        }

        $totalSubs = 0;
        $monthlyRevenue = [];
        $activeByUni = [];
        $recurringUnis = [];
        $lidoUnis = [];

        foreach ($subscriptions as $s) {
            $uni = (string) ($s['uniID'] ?? '');
            if ($uni === '') continue;
            $totalSubs++;
            $status = (string) ($s['status'] ?? '');
            $isActive = $status === 'on' && (int) ($s['expiry'] ?? 0) > $nowMs;
            if ($isActive) {
                $activeByUni[$uni] = true;
            }

            $payCount = $paymentCount[$uni] ?? 0;

            if ($isActive) {
                if ($payCount >= 2) $recurringUnis[$uni] = true;
                if ($payCount >= 2 || ($referralCount[$uni] ?? 0) > 0) $lidoUnis[$uni] = true;
            }

            $daysCfg = (int) ($s['count_days'] ?? 0);
            $amt = (float) ($s['amount'] ?? 0);
            $monthShare = $daysCfg > 0 ? $amt * (30.0 / $daysCfg) : 0;
            if ($isActive) {
                $created = (string) ($s['created_at'] ?? '');
                $ts = $created ? strtotime($created) : time();
                $monthKey = date('Y-m', $ts);
                $monthlyRevenue[$monthKey] = ($monthlyRevenue[$monthKey] ?? 0) + $monthShare;
            }
        }
        $recurring = \count($recurringUnis);
        $lidos = \count($lidoUnis);
        $activeSubs = \count($activeByUni);
        $ltvSum = array_sum($ltvByUser);

        uksort($monthlyRevenue, fn($a, $b) => strcmp((string)$a, (string)$b));

        $predictedMonthlyRevenue = !empty($monthlyRevenue) ? array_sum($monthlyRevenue) : 0.0;
        $recurringClientsList = [];
        $recurringPayIntervals = [];

        $actualMonthlyRevenue = 0.0;
        // actual = gross из ЮKassa (файловый кэш 1ч, БЕЗ сети — та же цифра что в прибыли).
        // Кэша нет (прибыль ещё не открывали) — fallback на локальный леджер.
        $kassaRate = 0.0;
        try { $kassaRate = (float) (Finance::getCosts()['commission'] ?? 0); } catch (\Throwable $e) {}
        $kassaCached = null;
        try { $kassaCached = \Setting\Route\Function\Controllers\Admin\Payments\PaymentInfo::cachedRevenue(30, $kassaRate); } catch (\Throwable $e) {}
        if (\is_array($kassaCached)) {
            $actualMonthlyRevenue = (float) ($kassaCached['gross'] ?? 0);
        } else {
            $cutoff30 = strtotime('-30 days');
            foreach ($paymentsByUniDateAll as $u => $pays) {
                foreach ($pays as $r) {
                    $ca = (string) ($r['created_at'] ?? '');
                    $ts = $ca ? strtotime($ca) : 0;
                    if ($ts && $ts >= $cutoff30) {
                        $actualMonthlyRevenue += (float) ($r['amount'] ?? 0);
                    }
                }
            }
        }
        $trialConversions = [];
        $trialSeen = [];
        foreach ($subscriptions as $s) {
            $uni = (string) ($s['uniID'] ?? '');
            if ($uni === '' || isset($trialSeen[$uni])) continue;
            $sub = (string) ($s['subscription'] ?? '');
            $isTrial = preg_match('/trial|free/i', $sub);
            if (!$isTrial) continue;
            if (!empty($paymentsByUniDateAll[$uni])) {
                $firstPay = $paymentsByUniDateAll[$uni][0];
                $trialStart = (string) ($s['created_at'] ?? '');
                $interval = '';
                if ($trialStart && !empty($firstPay['created_at'])) {
                    $diff = strtotime($firstPay['created_at']) - strtotime($trialStart);
                    if ($diff > 0) {
                        $days = (int) floor($diff / 86400);
                        $interval = $days > 0 ? "через {$days}д" : 'в день оплаты';
                    }
                }
                $trialConversions[] = [
                    'uniID' => $uni,
                    'trial_days' => (int) ($s['count_days'] ?? 0),
                    'first_payment' => $firstPay['created_at'],
                    'interval' => $interval,
                    'pay_count' => count($paymentsByUniDateAll[$uni]),
                    'pay_count_period' => $paymentCount[$uni] ?? 0,
                ];
                $trialSeen[$uni] = true;
            }
        }

        $priceOptimization = self::buildPriceOptimization($subscriptions, $nowMs);

        $recurringSeen = [];
        foreach ($subscriptions as $s) {
            $uni = (string) ($s['uniID'] ?? '');
            if ($uni === '') continue;
            $isActive = ((string) ($s['status'] ?? '') === 'on') && (int) ($s['expiry'] ?? 0) > $nowMs;
            if (!($isActive && ($paymentCount[$uni] ?? 0) >= 2)) continue;
            if (isset($recurringSeen[$uni])) continue;
            $recurringSeen[$uni] = true;
            $pays = $paymentsByUniDateAll[$uni] ?? [];
            $daysCfg = (int) ($s['count_days'] ?? 0);
            $amt = (float) ($s['amount'] ?? 0);
            $monthShare = $daysCfg > 0 ? $amt * (30.0 / $daysCfg) : 0;
            $nextPayDate = '';
            if (!empty($pays)) {
                $lastTs = strtotime((string) ($pays[count($pays) - 1]['created_at'] ?? ''));
                if ($lastTs) {
                    $nextPayDate = date('Y-m-d', strtotime('+' . ($daysCfg > 0 ? $daysCfg : 30) . ' days', $lastTs));
                }
            }
            $intervals = [];
            for ($i = 1, $n = count($pays); $i < $n; $i++) {
                $prev = strtotime((string) ($pays[$i - 1]['created_at'] ?? ''));
                $curr = strtotime((string) ($pays[$i]['created_at'] ?? ''));
                if ($prev && $curr && $curr > $prev) {
                    $intervals[] = ($curr - $prev) / 86400;
                }
            }
            if ($intervals) $recurringPayIntervals = array_merge($recurringPayIntervals, $intervals);
            $recurringClientsList[] = [
                'uniID' => $uni,
                'ltv' => round($ltvByUser[$uni] ?? 0, 2),
                'pay_count' => $paymentCount[$uni] ?? 0,
                'next_payment' => $nextPayDate,
                'expected_month' => $monthShare,
                'next_month_expectation' => [
                    'month' => $nextPayDate ? date('Y-m', strtotime($nextPayDate)) : null,
                    'expected' => $monthShare,
                ],
            ];
        }
        $avgRepeatDays = $recurringPayIntervals ? round(array_sum($recurringPayIntervals) / count($recurringPayIntervals), 1) : 0;

        arsort($ltvByUser);
        $topLtv = array_slice($ltvByUser, 0, 10, true);
        $topClients = [];
        foreach ($topLtv as $uni => $val) {
            $topClients[] = [
                'uniID' => $uni,
                'ltv' => round($val, 2),
                'pay_count' => $paymentCount[$uni] ?? 0,
                'referrals' => $referralCount[$uni] ?? 0,
                'active' => isset($activeByUni[$uni]),
            ];
        }

        if (empty($topClients)) {
            foreach ($activeByUni as $uni => $_) {
                $topClients[] = [
                    'uniID' => $uni,
                    'ltv' => 0,
                    'pay_count' => $paymentCount[$uni] ?? 0,
                    'referrals' => $referralCount[$uni] ?? 0,
                    'active' => true,
                ];
                if (count($topClients) >= 10) break;
            }
        }
        $avgLtv = $ltvSum > 0 && $activeSubs > 0 ? $ltvSum / $activeSubs : 0;

        return [
            'generated_at' => date('c'),
            'period_label' => $days !== null ? "за $days дней" : ($month !== null ? "месяц $month" : 'все время'),
            'metrics' => [
                'total_subscriptions' => $totalSubs,
                'active_subscriptions' => $activeSubs,
                'recurring_clients' => $recurring,
                'leader_clients' => $lidos,
                'total_ltv' => round($ltvSum, 2),
                'avg_ltv' => round($avgLtv, 2),
                'avg_payments_per_client' => $paymentCount
                    ? round(array_sum($paymentCount) / max(count($paymentCount), 1), 2) : 0,
                'predicted_monthly_revenue' => round($predictedMonthlyRevenue, 2),
                'forecast_min' => round($predictedMonthlyRevenue * 0.5, 2),
                'forecast_max' => round($predictedMonthlyRevenue * 0.9, 2),
                'actual_monthly_revenue' => round($actualMonthlyRevenue, 2),
                'active_payments_revenue' => round(array_sum((array) $monthlyRevenue), 2),
            ],
            'monthly_revenue' => $monthlyRevenue,
            'top_clients_by_ltv' => $topClients,
            'recurring_clients' => $recurringClientsList,
            'trial_conversions' => $trialConversions,
            'repeat_purchase_days' => $avgRepeatDays,
            'recurring_count' => $recurring,
            'price_optimization' => $priceOptimization,
        ];
    }

    /**
     * Умная подсказка цен: стоит ли поднимать тариф и до скольки, чтобы
     * зарабатывать с целевой маржой, а не работать в ноль.
     * Математика на клиента/мес: R — доход, c — комиссия кассы (доля),
     * F — доля серверных расходов. Чистыми: N = R·(1−c) − F, маржа = N/R.
     * Цель N/R ≥ m (30%): R ≥ F/(1−c−m). Цена подписки = R·days/30.
     */
    private const PRICE_TARGET_MARGIN = 0.30;

    /** Карта "устройства × дни" → ячейка PriceConfig (тариф + месяцев). */
    private static function priceCellMap(): array
    {
        $map = [];
        try {
            foreach (\Setting\Route\Function\Controllers\Kassa\PriceConfig::getTariffConfig() as $key => $c) {
                $map[(int) ($c['devices'] ?? 0) . 'x' . (int) ($c['days'] ?? 0)] = [
                    'key' => (string) $key,
                    'tariff' => (string) ($c['tariff'] ?? ''),
                    'months' => (int) ($c['months'] ?? 0),
                    'days' => (int) ($c['days'] ?? 0),
                    'devices' => (int) ($c['devices'] ?? 0),
                ];
            }
        } catch (\Throwable $e) {}
        return $map;
    }

    /** Текущая цена ячейки PriceConfig за весь срок (мес. цена × месяцев). */
    private static function priceCellFullPrice(string $tariff, int $months): ?float
    {
        try {
            $cfg = \Setting\Route\Function\Controllers\Kassa\PriceConfig::getConfig();
            $perMonth = $cfg[$tariff]['periods'][$months]['price'] ?? null;
            if ($perMonth === null) return null;
            return (float) $perMonth * $months;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function buildPriceOptimization(array $subscriptions, int $nowMs): array
    {
        $costs = Finance::monthlyCosts();
        $commRate = min(1.0, max(0.0, (float) (\Setting\Route\Function\Controllers\Admin\Finance\Finance::getCosts()['commission'] ?? 0.0)));
        $marginTarget = self::PRICE_TARGET_MARGIN;

        $byTariff = [];
        $activeCount = 0;
        $cellMap = self::priceCellMap();
        $keyByTM = [];
        foreach ($cellMap as $cm) {
            $keyByTM[(string) $cm['tariff'] . '|' . (int) $cm['months']] = (string) $cm['key'];
        }
        foreach ($subscriptions as $s) {
            $sub = (string) ($s['subscription'] ?? '');
            if ($sub === '' || $sub === 'free' || $sub === 'trial' || $sub === 'bonus') continue;
            $isActive = ((string) ($s['status'] ?? '') === 'on') && (int) ($s['expiry'] ?? 0) > $nowMs;
            if (!$isActive) continue;
            $activeCount++;
            // Нулевые суммы — бесплатные выдачи: делят серверные расходы,
            // но в статистику тарифов не идут (иначе тянут среднее в ноль)
            $amtRow = (float) ($s['amount'] ?? 0);
            if ($amtRow <= 0) continue;
            // Реальный тариф из PriceConfig по устройствам и дням, а не "Оплаченная"
            $cell = $cellMap[(int) ($s['count_devices'] ?? 0) . 'x' . (int) ($s['count_days'] ?? 0)] ?? null;
            if ($cell !== null) {
                $key = (string) $cell['tariff'] . ' · ' . (int) $cell['months'] . ' мес';
            } else {
                $key = 'Другое';
            }
            $byTariff[$key]['name'] = $key;
            $byTariff[$key]['count'] = (int) ($byTariff[$key]['count'] ?? 0) + 1;
            $byTariff[$key]['sum'] = (float) ($byTariff[$key]['sum'] ?? 0) + $amtRow;
            $byTariff[$key]['days'][] = (int) ($s['count_days'] ?? 0);
            if ($cell !== null && !isset($byTariff[$key]['cell'])) $byTariff[$key]['cell'] = $cell;
        }

        if (!$activeCount) {
            return [
                'costs' => $costs,
                'commission_rate' => $commRate,
                'margin_target' => $marginTarget,
                'tariffs' => [],
                'explain' => 'Нет активных платных подписок — данных для анализа недостаточно.',
            ];
        }

        $denom = 1.0 - $commRate - $marginTarget;
        $totalNetMonth = 0.0;
        $totalUpliftMonth = 0.0;
        // Шаг 1: каждая группа сама по себе (все суммы — за месяц на клиента)
        $calc = [];
        foreach ($byTariff as $name => $t) {
            $cnt = (int) $t['count'];
            $sum = (float) $t['sum'];
            $avg = $cnt > 0 ? $sum / $cnt : 0;
            $daysList = array_filter($t['days'] ?? [], fn($d) => $d > 0);
            $avgDays = $daysList ? array_sum($daysList) / count($daysList) : 30;
            $cell = $t['cell'] ?? null;
            $months = $cell !== null ? (int) $cell['months'] : 0;
            $cfgFull = ($cell !== null) ? self::priceCellFullPrice((string) $cell['tariff'], $months) : null;
            $cfgMonthly = ($cfgFull !== null && $months > 0) ? $cfgFull / $months : null;
            $revenueMonth = $avg * (30.0 / max($avgDays, 1));
            $commissionPerClient = $revenueMonth * $commRate;
            $costShare = $costs > 0 ? ($costs / $activeCount) : 0;
            $netPerClient = $revenueMonth - $commissionPerClient - $costShare;
            $margin = $revenueMonth > 0 ? $netPerClient / $revenueMonth : 0;
            $targetRevenueMonth = ($denom > 0 && $costShare > 0) ? $costShare / $denom : 0;
            $needMonthly = ($targetRevenueMonth > 0 && $revenueMonth < $targetRevenueMonth) ? $targetRevenueMonth : 0;
            $calc[$name] = [
                'name' => $name, 'cell' => $cell, 'months' => $months,
                'tariff' => $cell !== null ? (string) $cell['tariff'] : '',
                'cnt' => $cnt, 'avg' => $avg, 'avgDays' => $avgDays,
                'cfgFull' => $cfgFull, 'cfgMonthly' => $cfgMonthly,
                'revenueMonth' => $revenueMonth, 'commissionPerClient' => $commissionPerClient,
                'costShare' => $costShare, 'netPerClient' => $netPerClient, 'margin' => $margin,
                'targetRevenueMonth' => $targetRevenueMonth, 'needMonthly' => $needMonthly,
            ];
        }
        // Шаг 2: все ячейки PriceConfig в таблице — даже без покупок.
        // Лесенка цен: за месяц длинный срок дешевле короткого.
        // Если поднятие ломает порядок, подтягиваем короткие сроки (только вверх).
        try {
            $fullCfg = \Setting\Route\Function\Controllers\Kassa\PriceConfig::getConfig();
        } catch (\Throwable $e) { $fullCfg = []; }
        foreach ($fullCfg as $tn2 => $tconf2) {
            foreach (($tconf2['periods'] ?? []) as $mm2 => $pp2) {
                $en2 = (string) $tn2 . ' · ' . (int) $mm2 . ' мес';
                if (isset($calc[$en2])) continue;
                $cfgM2 = (float) ($pp2['price'] ?? 0);
                $calc[$en2] = [
                    'name' => $en2, 'tariff' => (string) $tn2, 'months' => (int) $mm2,
                    'cell' => ['tariff' => (string) $tn2, 'months' => (int) $mm2, 'days' => (int) ($pp2['days'] ?? 30)],
                    'cnt' => 0, 'avg' => 0.0, 'avgDays' => (float) ($pp2['days'] ?? 30),
                    'cfgFull' => round($cfgM2 * (int) $mm2, 2), 'cfgMonthly' => $cfgM2,
                    'revenueMonth' => 0.0, 'commissionPerClient' => 0.0,
                    'costShare' => $costs > 0 ? ($costs / max($activeCount, 1)) : 0,
                    'netPerClient' => 0.0, 'margin' => null,
                    'targetRevenueMonth' => 0.0, 'needMonthly' => 0.0,
                    'empty' => true,
                ];
            }
        }
        $tariffsSeen = [];
        foreach ($calc as $c) {
            if (($c['tariff'] ?? '') !== '') $tariffsSeen[(string) $c['tariff']] = true;
        }
        foreach (array_unique(array_merge(array_keys($fullCfg), array_keys($tariffsSeen))) as $tn) {
            $periods = $fullCfg[$tn]['periods'] ?? [];
            if (!$periods) continue;
            $mmList = array_keys($periods);
            sort($mmList, SORT_NUMERIC);
            $eff = [];
            foreach ($mmList as $mm) $eff[$mm] = (float) ($periods[$mm]['price'] ?? 0);
            $touched = false;
            foreach ($calc as $c) {
                if (($c['tariff'] ?? '') !== (string) $tn) continue;
                $mm = (int) ($c['months'] ?? 0);
                if ($mm > 0 && ($c['needMonthly'] ?? 0) > ($eff[$mm] ?? 0)) { $eff[$mm] = (float) $c['needMonthly']; $touched = true; }
            }
            if (!$touched) continue;
            for ($li = count($mmList) - 2; $li >= 0; $li--) {
                if ($eff[$mmList[$li]] < $eff[$mmList[$li + 1]]) $eff[$mmList[$li]] = $eff[$mmList[$li + 1]];
            }
            foreach ($calc as $nm => $c) {
                if (($c['tariff'] ?? '') !== (string) $tn) continue;
                $mm = (int) ($c['months'] ?? 0);
                if ($mm > 0 && $eff[$mm] > max((float) ($c['needMonthly'] ?? 0), (float) ($c['cfgMonthly'] ?? 0)) + 1e-9) {
                    $calc[$nm]['effMonthly'] = $eff[$mm];
                    $calc[$nm]['ladder'] = true;
                }
            }
        }
                // Шаг 3: итоговые строки
        foreach ($calc as $name => $c) {
            $cnt = (int) ($c['cnt'] ?? 0);
            $avg = (float) ($c['avg'] ?? 0);
            $avgDays = (float) ($c['avgDays'] ?? 30);
            $cell = $c['cell'] ?? null;
            $months = (int) ($c['months'] ?? 0);
            $cfgFull = $c['cfgFull'] ?? null;
            $revenueMonth = (float) ($c['revenueMonth'] ?? 0);
            $commissionPerClient = (float) ($c['commissionPerClient'] ?? 0);
            $costShare = (float) ($c['costShare'] ?? 0);
            $netPerClient = (float) ($c['netPerClient'] ?? 0);
            $margin = $c['margin'] ?? null;
            $needMonthly = (float) ($c['needMonthly'] ?? 0);
            $isLadder = !empty($c['ladder']);
            $isEmpty = !empty($c['empty']);
            $effMonthly = isset($c['effMonthly']) ? (float) $c['effMonthly'] : $needMonthly;
            if ($cell !== null && $months > 0) {
                $newPrice = $effMonthly > 0 ? (float) (int) round($effMonthly * $months) : 0.0;
            } else {
                $newPrice = $effMonthly > 0 ? (float) (int) round($effMonthly * max($avgDays, 1) / 30.0) : 0.0;
            }
            $advise = $newPrice > 0 && ($cell === null ? $effMonthly > $revenueMonth + 1e-9 : $effMonthly > (float) ($cfgFull !== null && $months > 0 ? $cfgFull / $months : 0) + 1e-9);
            $upliftMonth = ($advise && $cnt > 0) ? max(0.0, $effMonthly - $revenueMonth) * $cnt : 0.0;
            if ($isEmpty) {
                if ($effMonthly > (float) ($cfgFull !== null && $months > 0 ? $cfgFull / $months : 0) + 1e-9) {
                    $status = 'ladder';
                    $reason = 'Пустой период: клиентов нет, но лесенка требует поднять и его — иначе длинный срок станет дороже за месяц. Рекомендуемая цена ' . number_format($newPrice, 2, '.', '') . '₽.';
                } else {
                    $status = 'empty';
                    $reason = 'Нет активных клиентов — не на что опереться. Текущая цена ' . number_format((float) ($cfgFull ?? 0), 2, '.', '') . '₽.';
                }
            } elseif (($c['netPerClient'] ?? 0) < 0 && $cnt > 0) {
                $status = 'loss';
                $reason = 'Убыток ' . number_format((float) abs($netPerClient), 2, '.', '') . '₽/мес с клиента.';
            } elseif ($advise) {
                $status = $isLadder ? 'ladder' : 'low';
                $reason = $isLadder
                    ? 'Подтянуто под лесенку: короткий срок не может стоить меньше за месяц. Новая цена ' . number_format($newPrice, 2, '.', '') . '₽ даст +' . number_format((float) $upliftMonth, 2, '.', '') . '₽/мес.'
                    : 'Маржа ' . number_format((float) (($margin ?? 0) * 100), 1, '.', '') . '% ниже цели ' . (int) ($marginTarget * 100) . '%. Поднятие до ' . number_format($newPrice, 2, '.', '') . '₽ даст +' . number_format((float) $upliftMonth, 2, '.', '') . '₽/мес.';
            } else {
                $status = 'ok';
                $reason = 'Маржа ' . number_format((float) (($margin ?? 0) * 100), 1, '.', '') . '% — цель ' . (int) ($marginTarget * 100) . '% выполнена. Менять цену не нужно.';
            }
            if ($cell !== null) {
                $reason .= ' Сейчас в PriceConfig: ' . number_format((float) ($cfgFull ?? 0), 2, '.', '') . '₽.';
            }
            $simulations = [];
            if ($cnt > 0) {
                foreach ([-0.10, 0.10] as $delta) {
                    $simPrice = $avg * (1 + $delta);
                    $simRev = $simPrice * (30.0 / max($avgDays, 1));
                    $simComm = $simRev * $commRate;
                    $simNet = $simRev - $simComm - ($costs > 0 ? $costShare : 0);
                    $simulations[] = [
                        'price' => round($simPrice, 2),
                        'delta' => ($delta * 100) . '%',
                        'revenue_month' => round($simRev, 2),
                        'commission' => round($simComm, 2),
                        'net' => round($simNet, 2),
                        'margin' => $simRev > 0 ? round($simNet / $simRev * 100, 1) : 0,
                    ];
                }
            }
            $totalNetMonth += $netPerClient * $cnt;
            $totalUpliftMonth += $upliftMonth;
            $isLoss = $status === 'loss';
            $byTariff[$name]['name'] = (string) ($c['name'] ?? $name);
            $byTariff[$name]['count'] = $cnt;
            $byTariff[$name]['old_price'] = round($avg, 2);
            $byTariff[$name]['new_price'] = $newPrice;
            $byTariff[$name]['avg'] = $avg;
            $byTariff[$name]['avg_days'] = $avgDays;
            $byTariff[$name]['revenue_month'] = round($revenueMonth, 2);
            $byTariff[$name]['is_loss'] = $isLoss;
            $byTariff[$name]['status'] = $status;
            $byTariff[$name]['net_per_client'] = round($netPerClient, 2);
            $byTariff[$name]['margin'] = $margin === null ? null : round($margin * 100, 1);
            $byTariff[$name]['target_revenue_month'] = round((float) ($c['targetRevenueMonth'] ?? $needMonthly), 2);
            $byTariff[$name]['uplift_month'] = round($upliftMonth, 2);
            $byTariff[$name]['cost_share'] = round($costShare, 2);
            $byTariff[$name]['commission_per_client'] = round($commissionPerClient, 2);
            $byTariff[$name]['config_price'] = $cfgFull !== null ? round($cfgFull, 2) : null;
            $byTariff[$name]['cell_key'] = $keyByTM[(string) ($c['tariff'] ?? '') . '|' . (int) $months] ?? null;
            $byTariff[$name]['recommended_price'] = (int) $newPrice;
            $byTariff[$name]['reason'] = $reason;
            $byTariff[$name]['simulations'] = $simulations;
        }
        $ordered = [];
        foreach ($fullCfg as $tn3 => $tconf3) {
            foreach (array_keys($tconf3['periods'] ?? []) as $mm3) {
                $nm3 = (string) $tn3 . ' · ' . (int) $mm3 . ' мес';
                if (isset($byTariff[$nm3])) { $ordered[$nm3] = $byTariff[$nm3]; unset($byTariff[$nm3]); }
            }
        }
        foreach ($byTariff as $nm3 => $t3) $ordered[$nm3] = $t3;
        $byTariff = $ordered;
        $badCount = 0;
        $ladderCount = 0;
        foreach ($byTariff as $t) {
            if (($t['status'] ?? 'ok') === 'loss' || ($t['status'] ?? 'ok') === 'low') $badCount++;
            if (($t['status'] ?? '') === 'ladder') $ladderCount++;
        }
        $explain = $badCount > 0
            ? 'Тарифов ниже цели ' . $badCount . ' из ' . \count($byTariff) . ': поднимаем цену до маржи ' . (int) ($marginTarget * 100) . '%. Потенциал +' . number_format($totalUpliftMonth, 2, '.', '') . '₽/мес.'
            : 'Все тарифы держат маржу ' . (int) ($marginTarget * 100) . '%. Цены можно не менять.';
        if ($ladderCount > 0) $explain .= ' Плюс лесенка: ' . $ladderCount . ' период(а) поднять за компанию.';
        return [
            'costs' => $costs,
            'commission_rate' => $commRate,
            'margin_target' => $marginTarget,
            'active_paid_clients' => $activeCount,
            'total_net_month' => round($totalNetMonth, 2),
            'total_uplift_month' => round($totalUpliftMonth, 2),
            'tariffs' => array_values($byTariff),
            'explain' => $explain,
        ];
    }
}
