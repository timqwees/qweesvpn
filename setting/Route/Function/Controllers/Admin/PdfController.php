<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Admin;

use App\Config\Database;
use Mpdf\Mpdf;

/**
 * ПРОСТОЙ PDF экспорт для админ панели
 * 
 * КАК ИСПОЛЬЗОВАТЬ:
 * 1. Готовый отчет: PdfController::exportTable('qwees_users')
 * 2. Свой HTML: PdfController::exportHtml($myHtml, 'myfile.pdf')
 * 3. Свои данные: PdfController::exportData($array, 'title', 'file.pdf')
 * 4. Новый метод: просто добавить в этот класс
 * 
 * composer require mpdf/mpdf
 */
class PdfController
{
    private Mpdf $mpdf;

    public function __construct()
    {
        $this->mpdf = new Mpdf(['default_font' => 'dejavusans']);
    }

    // ======== ОСНОВНЫЕ МЕТОДЫ ========

    /**
     * Главный метод для HTTP роутинга
     */
    public function handleExport(): void
    {
        $type = $_GET['type'] ?? '';
        $table = $_GET['table'] ?? '';
        $design = $_GET['design'] ?? 'minimal';

        try {
            switch ($type) {
                case 'table':
                    $this->exportTable($table, $design);
                    break;
                case 'subscriptions':
                    $this->exportSubscriptionStats($design);
                    break;
                case 'users':
                    $this->exportUserStats($design);
                    break;
                case 'charts':
                    $this->exportCharts($design);
                    break;
                case 'about':
                    $this->exportAboutPage($design);
                    break;
                case 'requisites':
                    $this->exportRequisitesPage($design);
                    break;
                default:
                    $this->exportHtml('Неизвестный тип', '<p>Укажите правильный тип экспорта</p>', 'error.pdf');
            }
        } catch (\Exception $e) {
            header('Location: /admin/database' . ($table ? '?table=' . $table : '') . '&error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Экспорт любого HTML в PDF
     */
    public function exportHtml(string $title, string $content, string $filename = ''): void
    {
        $html = $this->getTemplate($title, $content, 'minimal');
        $this->downloadPdf($html, $filename ?: 'report.pdf', $title);
    }

    /**
     * Экспорт массива данных в таблицу
     */
    public function exportData(array $data, string $title, string $filename, string $design = 'minimal'): void
    {
        if (empty($data)) {
            $this->exportHtml($title, '<p>Нет данных</p>', $filename);
            return;
        }

        $tableHtml = $this->createTable($data);
        $html = $this->getTemplate($title, $tableHtml, $design);
        $this->downloadPdf($html, $filename, $title);
    }

    // ======== ГОТОВЫЕ ОТЧЕТЫ ========

    /**
     * Экспорт таблицы из БД
     */
    public function exportTable(string $table, string $design = 'minimal'): void
    {
        $data = AdminDatabase::getData($table, 1000);
        $this->exportData($data, "Отчет: {$table}", "{$table}_" . date('Y-m-d') . '.pdf', $design);
    }

    /**
     * Статистика подписок
     */
    public function exportSubscriptionStats(string $design = 'minimal'): void
    {
        $total = AdminDatabase::getCount('qwees_subscriptions');
        $active = Database::send("SELECT COUNT(*) as c FROM qwees_subscriptions WHERE status='on'")[0]['c'] ?? 0;
        $expired = Database::send("SELECT COUNT(*) as c FROM qwees_subscriptions WHERE status='off'")[0]['c'] ?? 0;

        $content = "
            <h2>Общая статистика</h2>
            <p>Всего подписок: <strong>{$total}</strong></p>
            <p>Активных: <strong>{$active}</strong></p>
            <p>Истекших: <strong>{$expired}</strong></p>
        ";

        $this->exportHtml('Статистика подписок', $content, 'subscriptions_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Статистика пользователей
     */
    public function exportUserStats(string $design = 'minimal'): void
    {
        $users = AdminDatabase::getCount('qwees_users');
        $content = "<p>Всего пользователей: <strong>{$users}</strong></p>";
        $this->exportHtml('Статистика пользователей', $content, 'users_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Экспорт графиков и аналитики
     */
    public function exportCharts(string $design = 'minimal'): void
    {
        // Получаем данные для графиков (аналогично index.php)
        $stats = [
            'usersWithSubscriptions' => Database::send("SELECT COUNT(*) as c FROM qwees_users WHERE uniID IN (SELECT uniID FROM qwees_subscriptions WHERE status='on')")[0]['c'] ?? 0,
            'usersWithoutSubscriptions' => Database::send("SELECT COUNT(*) as c FROM qwees_users WHERE uniID NOT IN (SELECT uniID FROM qwees_subscriptions WHERE status='on')")[0]['c'] ?? 0,
        ];

        // Финансовая статистика
        if (Database::isMysql()) {
            $monthlyRevenue = Database::send("SELECT COALESCE(SUM(amount), 0) as total FROM qwees_subscriptions WHERE date_end >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND status='on'")[0]['total'] ?? 0;
            $weeklyRevenue = Database::send("SELECT COALESCE(SUM(amount), 0) as total FROM qwees_subscriptions WHERE date_end >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status='on'")[0]['total'] ?? 0;
            $dailyRevenue = Database::send("SELECT COALESCE(SUM(amount), 0) as total FROM qwees_subscriptions WHERE date_end >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND status='on'")[0]['total'] ?? 0;
            $monthlyDataSql = "
                SELECT 
                    DATE_FORMAT(date_end, '%Y-%m') as month,
                    COUNT(*) as users_count
                FROM qwees_subscriptions 
                WHERE date_end >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(date_end, '%Y-%m')
                ORDER BY month DESC
                LIMIT 12
            ";
        } else {
            $monthlyRevenue = Database::send("SELECT COALESCE(SUM(amount), 0) as total FROM qwees_subscriptions WHERE date_end >= date('now', '-30 days') AND status='on'")[0]['total'] ?? 0;
            $weeklyRevenue = Database::send("SELECT COALESCE(SUM(amount), 0) as total FROM qwees_subscriptions WHERE date_end >= date('now', '-7 days') AND status='on'")[0]['total'] ?? 0;
            $dailyRevenue = Database::send("SELECT COALESCE(SUM(amount), 0) as total FROM qwees_subscriptions WHERE date_end >= date('now', '-1 day') AND status='on'")[0]['total'] ?? 0;
            $monthlyDataSql = "
                SELECT 
                    strftime('%Y-%m', date_end) as month,
                    COUNT(*) as users_count
                FROM qwees_subscriptions 
                WHERE date_end >= date('now', '-12 months')
                GROUP BY strftime('%Y-%m', date_end)
                ORDER BY month DESC
                LIMIT 12
            ";
        }
        $avgCheck = Database::send("SELECT COALESCE(AVG(amount), 0) as avg FROM qwees_subscriptions WHERE status='on'")[0]['avg'] ?? 0;

        $monthlyData = Database::send($monthlyDataSql);

        // Данные по тарифам
        $planData = Database::send("
            SELECT 
                subscription,
                COUNT(*) as count,
                SUM(amount) as revenue
            FROM qwees_subscriptions 
            WHERE status='on'
            GROUP BY subscription
            ORDER BY revenue DESC
        ");

        // Формируем контент
        $content = "
            <h2>Общая статистика пользователей</h2>
            <table>
                <tr><th>Категория</th><th>Количество</th></tr>
                <tr><td>С подписками</td><td>{$stats['usersWithSubscriptions']}</td></tr>
                <tr><td>Без подписок</td><td>{$stats['usersWithoutSubscriptions']}</td></tr>
            </table>

            <h2>Финансовая статистика</h2>
            <table>
                <tr><th>Период</th><th>Прибыль (₽)</th></tr>
                <tr><td>За месяц</td><td>" . number_format($monthlyRevenue, 2) . "</td></tr>
                <tr><td>За неделю</td><td>" . number_format($weeklyRevenue, 2) . "</td></tr>
                <tr><td>За день</td><td>" . number_format($dailyRevenue, 2) . "</td></tr>
                <tr><td>Средний чек</td><td>" . number_format($avgCheck, 2) . "</td></tr>
            </table>
        ";

        // Добавляем данные по месяцам
        if (!empty($monthlyData)) {
            $content .= "<h2>Пользователи по месяцам</h2><table><tr><th>Месяц</th><th>Новых пользователей</th></tr>";
            foreach ($monthlyData as $row) {
                $content .= "<tr><td>{$row['month']}</td><td>{$row['users_count']}</td></tr>";
            }
            $content .= "</table>";
        }

        // Добавляем данные по тарифам
        if (!empty($planData)) {
            $content .= "<h2>Прибыль по тарифам</h2><table><tr><th>Тариф</th><th>Количество</th><th>Прибыль (₽)</th></tr>";
            foreach ($planData as $row) {
                $content .= "<tr><td>{$row['subscription']}</td><td>{$row['count']}</td><td>" . number_format($row['revenue'], 2) . "</td></tr>";
            }
            $content .= "</table>";
        }

        $this->exportHtml('Графики и аналитика', $content, 'charts_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Экспорт страницы "О компании"
     */
    public function exportAboutPage(string $design = 'minimal'): void
    {
        $site = \Setting\Route\Function\Functions::site();
        $content = "
            <h1 style='text-align: center; color: #1f2937; font-size: 28px; margin-bottom: 10px;'>" . htmlspecialchars($site['ООО']) . "</h1>
            <p style='text-align: center; color: #6b7280; font-size: 14px; margin-bottom: 30px;'>Ваш личный Орел в мире VPN</p>
            
            <div style='background: linear-gradient(135deg, #fef3c7, #fde68a); padding: 25px; border-radius: 12px; margin: 30px 0; border-left: 4px solid #f59e0b;'>
                <h2 style='color: #b45309; margin-top: 0; font-size: 20px;'>🦅 Мы — Ваш Орел!</h2>
                <p style='line-height: 1.8; color: #78350f; font-size: 14px; margin-top: 10px;'><strong>Как орлы, мы быстрые, мощные и видим все издалека.</strong></p>
                <p style='line-height: 1.6; color: #92400e; font-size: 13px;'>Парим высоко над ограничениями, быстро реагируем на угрозы и защищаем вас с хищной точностью.</p>
            </div>
            
            <h2 style='color: #10b981; border-bottom: 2px solid #10b981; padding-bottom: 8px; font-size: 18px;'>✨ Почему миллионы выбирают нас</h2>
            <p style='line-height: 1.8; color: #374151; font-size: 14px;'><strong style='color: #3b82f6;'>2026 год.</strong> Санкции, блокировки, ограничения. Обычные VPN не справляются.</p>
            <p style='line-height: 1.8; color: #374151; font-size: 14px;'>Мы создали <strong>" . htmlspecialchars($site['ООО']) . "</strong>, который работает всегда.</p>
            
            <table style='width: 100%; margin: 20px 0; border-collapse: separate; border-spacing: 10px;'>
                <tr>
                    <td style='padding: 15px; background: linear-gradient(135deg, #fef9c3, #fde047); border-radius: 8px; text-align: center; width: 50%;'>
                        <span style='font-size: 24px;'>⚡</span>
                        <p style='margin: 5px 0; color: #854d0e; font-weight: bold; font-size: 14px;'>Мгновенный старт</p>
                        <p style='margin: 0; color: #a16207; font-size: 12px;'>Подключение за 2 секунды</p>
                    </td>
                    <td style='padding: 15px; background: linear-gradient(135deg, #dcfce7, #86efac); border-radius: 8px; text-align: center; width: 50%;'>
                        <span style='font-size: 24px;'>∞</span>
                        <p style='margin: 5px 0; color: #166534; font-weight: bold; font-size: 14px;'>Безлимитный трафик</p>
                        <p style='margin: 0; color: #15803d; font-size: 12px;'>Качайте без ограничений</p>
                    </td>
                </tr>
                <tr>
                    <td style='padding: 15px; background: linear-gradient(135deg, #dbeafe, #93c5fd); border-radius: 8px; text-align: center;'>
                        <span style='font-size: 24px;'>🛡️</span>
                        <p style='margin: 5px 0; color: #1e40af; font-weight: bold; font-size: 14px;'>Военная защита</p>
                        <p style='margin: 0; color: #1d4ed8; font-size: 12px;'>AES-256 + новые протоколы</p>
                    </td>
                    <td style='padding: 15px; background: linear-gradient(135deg, #f3e8ff, #d8b4fe); border-radius: 8px; text-align: center;'>
                        <span style='font-size: 24px;'>🎧</span>
                        <p style='margin: 5px 0; color: #6b21a8; font-weight: bold; font-size: 14px;'>Поддержка 24/7</p>
                        <p style='margin: 0; color: #7e22ce; font-size: 12px;'>Всегда на связи</p>
                    </td>
                </tr>
            </table>
            
            <h2 style='color: #8b5cf6; border-bottom: 2px solid #8b5cf6; padding-bottom: 8px; font-size: 18px; margin-top: 25px;'>👨‍💻 Гений за продуктом</h2>
            <p style='line-height: 1.8; color: #374151; font-size: 14px;'><strong style='color: #8b5cf6; font-size: 16px;'>" . htmlspecialchars($site['контакты']['Директор']) . "</strong> — разработчик, который разочаровался в существующих VPN и создал идеальный.</p>
            <p style='line-height: 1.6; color: #374151; font-size: 14px;'>Его миссия — дать каждому <strong>свободный интернет</strong>.</p>
            
            <div style='background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #8b5cf6;'>
                <p style='line-height: 1.6; color: #374151; font-style: italic; font-size: 14px; margin: 0;'>&quot;Я хотел VPN, который просто работает. Без лагов, без страха, без ограничений.&quot;</p>
                <p style='text-align: right; color: #8b5cf6; font-size: 12px; margin: 10px 0 0 0;'>— " . htmlspecialchars($site['контакты']['Директор']) . ", Основатель</p>
            </div>
            
            <h2 style='color: #06b6d4; border-bottom: 2px solid #06b6d4; padding-bottom: 8px; font-size: 18px; margin-top: 25px;'>🚀 " . htmlspecialchars($site['ООО']) . " Studio</h2>
            <p style='line-height: 1.8; color: #374151; font-size: 14px;'>Элитная лаборатория, где рождаются <strong>технологии будущего</strong>.</p>
            <p style='line-height: 1.6; color: #374151; font-size: 14px;'>Мы не следуем трендам — <strong>мы их создаем</strong>.</p>
            
            <div style='text-align: center; margin-top: 15px;'>
                <span style='display: inline-block; padding: 5px 12px; background: linear-gradient(135deg, #fee2e2, #fecaca); color: #991b1b; border-radius: 20px; font-size: 12px; margin: 3px;'>Премиум VPN</span>
                <span style='display: inline-block; padding: 5px 12px; background: linear-gradient(135deg, #cffafe, #a5f3fc); color: #155e75; border-radius: 20px; font-size: 12px; margin: 3px;'>Топ-1 в 2026</span>
                <span style='display: inline-block; padding: 5px 12px; background: linear-gradient(135deg, #dcfce7, #86efac); color: #166534; border-radius: 20px; font-size: 12px; margin: 3px;'>50+ Серверов</span>
                <span style='display: inline-block; padding: 5px 12px; background: linear-gradient(135deg, #f3e8ff, #d8b4fe); color: #6b21a8; border-radius: 20px; font-size: 12px; margin: 3px;'>99.9% Uptime</span>
            </div>
            
            <div style='text-align: center; margin-top: 30px; padding: 25px; background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: 12px;'>
                <p style='font-size: 18px; font-style: italic; color: #78350f; margin-bottom: 10px; line-height: 1.6;'>&quot;Не позволяйте границам ограничивать <strong style='color: #b45309;'>вашу свободу</strong>.<br>С " . htmlspecialchars($site['ООО']) . " <strong style='color: #92400e;'>весь мир</strong> у вас в кармане&quot;</p>
                <p style='color: #b45309; font-size: 14px; margin-top: 15px;'>— Присоединяйтесь к революции " . htmlspecialchars($site['контакты']['Директор']) . "</p>
            </div>
            
            <h3 style='text-align: center; color: #6b7280; margin-top: 30px; font-size: 16px;'>📊 Наша статистика</h3>
            <table style='width: 100%; margin-top: 15px; border-collapse: separate; border-spacing: 10px;'>
                <tr>
                    <td style='text-align: center; padding: 20px 15px; background: linear-gradient(135deg, #dcfce7, #86efac); border-radius: 8px;'><strong style='font-size: 28px; color: #166534;'>50+</strong><br><span style='font-size: 12px; color: #15803d;'>Стран</span></td>
                    <td style='text-align: center; padding: 20px 15px; background: linear-gradient(135deg, #dbeafe, #93c5fd); border-radius: 8px;'><strong style='font-size: 22px; color: #1e40af;'>AES-256</strong><br><span style='font-size: 12px; color: #1d4ed8;'>Шифрование</span></td>
                    <td style='text-align: center; padding: 20px 15px; background: linear-gradient(135deg, #f3e8ff, #d8b4fe); border-radius: 8px;'><strong style='font-size: 28px; color: #6b21a8;'>24/7</strong><br><span style='font-size: 12px; color: #7e22ce;'>Поддержка</span></td>
                    <td style='text-align: center; padding: 20px 15px; background: linear-gradient(135deg, #dcfce7, #86efac); border-radius: 8px;'><strong style='font-size: 28px; color: #166534;'>99.9%</strong><br><span style='font-size: 12px; color: #15803d;'>Uptime</span></td>
                </tr>
            </table>
        ";

        $this->exportHtml('О компании ' . htmlspecialchars($site['ООО']), $content, 'about_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Экспорт страницы "Реквизиты компании"
     */
    public function exportRequisitesPage(string $design = 'minimal'): void
    {
        $site = \Setting\Route\Function\Functions::site();
        $content = "
            <h1 style='text-align: center; color: #1f2937; font-size: 24px; margin-bottom: 10px;'>Реквизиты компании</h1>
            <p style='text-align: center; color: #6b7280; font-size: 14px; margin-bottom: 30px;'>" . htmlspecialchars($site['информация']['Полное название']) . "</p>
            
            <h2 style='color: #10b981; border-bottom: 2px solid #10b981; padding-bottom: 8px; margin-top: 25px;'>Основная информация</h2>
            <table style='width: 100%; margin-top: 15px; border-collapse: collapse;'>
                <tr style='border-bottom: 1px solid #e5e7eb;'>
                    <td style='padding: 12px 0; color: #6b7280; width: 40%;'>Полное название</td>
                    <td style='padding: 12px 0; color: #1f2937; font-weight: 600;'>" . htmlspecialchars($site['информация']['Полное название']) . "</td>
                </tr>
                <tr style='border-bottom: 1px solid #e5e7eb;'>
                    <td style='padding: 12px 0; color: #6b7280;'>ИНН</td>
                    <td style='padding: 12px 0; color: #10b981; font-weight: 600; font-family: monospace;'>" . htmlspecialchars($site['информация']['ИНН']) . "</td>
                </tr>
                <tr style='border-bottom: 1px solid #e5e7eb;'>
                    <td style='padding: 12px 0; color: #6b7280;'>ОГРН</td>
                    <td style='padding: 12px 0; color: #10b981; font-weight: 600; font-family: monospace;'>" . htmlspecialchars($site['информация']['ОГРН']) . "</td>
                </tr>
                <tr style='border-bottom: 1px solid #e5e7eb;'>
                    <td style='padding: 12px 0; color: #6b7280;'>КПП</td>
                    <td style='padding: 12px 0; color: #10b981; font-weight: 600; font-family: monospace;'>" . htmlspecialchars($site['информация']['КПП']) . "</td>
                </tr>
            </table>
            
            <h2 style='color: #06b6d4; border-bottom: 2px solid #06b6d4; padding-bottom: 8px; margin-top: 25px;'>Банковские реквизиты</h2>
            <table style='width: 100%; margin-top: 15px; border-collapse: collapse;'>
                <tr style='border-bottom: 1px solid #e5e7eb;'>
                    <td style='padding: 12px 0; color: #6b7280; width: 40%;'>Банк</td>
                    <td style='padding: 12px 0; color: #1f2937; font-weight: 600;'>" . htmlspecialchars($site['банк']['Банк']) . "</td>
                </tr>
                <tr style='border-bottom: 1px solid #e5e7eb;'>
                    <td style='padding: 12px 0; color: #6b7280;'>БИК</td>
                    <td style='padding: 12px 0; color: #06b6d4; font-weight: 600; font-family: monospace;'>" . htmlspecialchars($site['банк']['БИК']) . "</td>
                </tr>
                <tr style='border-bottom: 1px solid #e5e7eb;'>
                    <td style='padding: 12px 0; color: #6b7280;'>Расчетный счет</td>
                    <td style='padding: 12px 0; color: #06b6d4; font-weight: 600; font-family: monospace; font-size: 12px;'>" . htmlspecialchars($site['банк']['Расчетный счет']) . "</td>
                </tr>
                <tr style='border-bottom: 1px solid #e5e7eb;'>
                    <td style='padding: 12px 0; color: #6b7280;'>Корр. счет</td>
                    <td style='padding: 12px 0; color: #06b6d4; font-weight: 600; font-family: monospace; font-size: 12px;'>" . htmlspecialchars($site['банк']['Корр. счет']) . "</td>
                </tr>
            </table>
            
            <h2 style='color: #8b5cf6; border-bottom: 2px solid #8b5cf6; padding-bottom: 8px; margin-top: 25px;'>Контактная информация</h2>
            <table style='width: 100%; margin-top: 15px; border-collapse: collapse;'>
                <tr style='border-bottom: 1px solid #e5e7eb;'>
                    <td style='padding: 12px 0; color: #6b7280; width: 40%;'>Директор</td>
                    <td style='padding: 12px 0; color: #1f2937; font-weight: 600;'>" . htmlspecialchars($site['контакты']['Директор']) . "</td>
                </tr>
                <tr style='border-bottom: 1px solid #e5e7eb;'>
                    <td style='padding: 12px 0; color: #6b7280;'>Email</td>
                    <td style='padding: 12px 0; color: #8b5cf6; font-weight: 600;'>" . htmlspecialchars($site['контакты']['Почта']) . "</td>
                </tr>
                <tr style='border-bottom: 1px solid #e5e7eb;'>
                    <td style='padding: 12px 0; color: #6b7280;'>Телефон</td>
                    <td style='padding: 12px 0; color: #1f2937; font-weight: 600;'>" . htmlspecialchars($site['контакты']['Телефон']) . "</td>
                </tr>
                <tr style='border-bottom: 1px solid #e5e7eb;'>
                    <td style='padding: 12px 0; color: #6b7280;'>Сайт</td>
                    <td style='padding: 12px 0; color: #8b5cf6; font-weight: 600;'>" . htmlspecialchars($site['baseUrl']) . "</td>
                </tr>
            </table>
            
            <div style='background: linear-gradient(135deg, #fef3c7, #fde68a); padding: 20px; border-radius: 12px; margin-top: 30px; border-left: 4px solid #f59e0b;'>
                <h3 style='color: #b45309; margin-top: 0; margin-bottom: 10px;'>⚠️ Важная информация</h3>
                <p style='color: #78350f; line-height: 1.6; margin: 0;'>
                    Все реквизиты актуальны и действительны для заключения договоров, выставления счетов и проведения платежей. 
                    При оплате услуг, пожалуйста, указывайте назначение платежа корректно.
                </p>
            </div>
            
            <div style='text-align: center; margin-top: 40px; padding: 20px; background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-radius: 12px;'>
                <p style='color: #166534; font-size: 14px; margin: 0;'>
                    <strong>" . htmlspecialchars($site['ООО']) . "</strong> — ваш надежный партнер в мире цифровой безопасности
                </p>
            </div>
        ";

        $this->exportHtml('Реквизиты компании ' . htmlspecialchars($site['ООО']), $content, 'requisites_' . date('Y-m-d') . '.pdf');
    }

    // ======== ВНУТРЕННИЕ МЕТОДЫ ========

    /**
     * Создать HTML таблицу из массива данных
     */
    private function createTable(array $data): string
    {
        if (empty($data)) {
            return '<p>Нет данных</p>';
        }

        $columns = array_keys($data[0]);
        $html = '<table><thead><tr>';

        // Заголовки
        foreach ($columns as $col) {
            $html .= '<th>' . htmlspecialchars($col) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        // Данные
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($columns as $col) {
                $val = (string) ($row[$col] ?? '');
                if (strlen($val) > 60) {
                    $val = substr($val, 0, 60) . '...';
                }
                $html .= '<td>' . htmlspecialchars($val) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Получить HTML шаблон
     */
    private function getTemplate(string $title, string $content, string $design = 'minimal'): string
    {
        $date = date('d.m.Y H:i');
        $year = date('Y');

        // Минималистичный шаблон
        $minimal = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: dejavusans, sans-serif; font-size: 11px; line-height: 1.5; color: #333; }
        h1 { font-size: 18px; font-weight: 600; color: #1f2937; margin-bottom: 8px; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; }
        h2 { font-size: 14px; font-weight: 600; color: #374151; margin-top: 20px; }
        p { margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th { background: #f3f4f6; padding: 8px; text-align: left; font-weight: 600; border-bottom: 2px solid #e5e7eb; font-size: 10px; }
        td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        tr:nth-child(even) { background: #fafafa; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #e5e7eb; font-size: 9px; color: #9ca3af; text-align: center; }
        .header-info { color: #6b7280; font-size: 10px; margin-bottom: 16px; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <div class="header-info">Сгенерировано: {$date}</div>
    {$content}
    <div class="footer">© {$year} QweesCore Admin</div>
</body>
</html>
HTML;

        // Роскошный шаблон
        $rich = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: dejavusans, sans-serif; font-size: 11px; line-height: 1.5; color: #1f2937; }
        h1 { font-size: 22px; font-weight: 300; color: #1f2937; margin-bottom: 12px; letter-spacing: -0.5px; }
        .gold-line { height: 3px; background: linear-gradient(90deg, #fbbf24, #f59e0b, #d97706); margin-bottom: 20px; }
        h2 { font-size: 14px; font-weight: 500; color: #451a03; margin-top: 24px; border-left: 3px solid #f59e0b; padding-left: 10px; }
        p { margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th { background: #fef3c7; padding: 10px; text-align: left; font-weight: 500; border-bottom: 2px solid #f59e0b; font-size: 10px; color: #78350f; }
        td { padding: 8px 10px; border-bottom: 1px solid #fde68a; font-size: 10px; }
        tr:nth-child(even) { background: #fffbeb; }
        .stat-box { background: linear-gradient(135deg, #fffbeb, #fef3c7); border-left: 4px solid #f59e0b; padding: 16px; margin: 12px 0; border-radius: 4px; }
        .stat-number { font-size: 28px; font-weight: 300; color: #b45309; }
        .stat-label { font-size: 11px; color: #92400e; text-transform: uppercase; letter-spacing: 0.5px; }
        .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #fde68a; font-size: 9px; color: #a8a29e; text-align: center; }
        .header-info { color: #78716c; font-size: 10px; margin-bottom: 20px; font-style: italic; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <div class="gold-line"></div>
    <div class="header-info">Документ сгенерирован {$date}</div>
    {$content}
    <div class="footer">Confidential • QweesCore Premium {$year}</div>
</body>
</html>
HTML;

        // Корпоративный шаблон
        $corporate = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: dejavusans, sans-serif; font-size: 10px; line-height: 1.4; color: #334155; }
        .header-bar { background: #1e40af; height: 8px; margin: -15px -15px 20px -15px; }
        h1 { font-size: 16px; font-weight: 700; color: #1e3a8a; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 1px; }
        .subtitle { color: #64748b; font-size: 10px; margin-bottom: 20px; }
        h2 { font-size: 12px; font-weight: 700; color: #1e40af; margin-top: 20px; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th { background: #1e40af; color: white; padding: 8px 10px; text-align: left; font-weight: 600; font-size: 9px; text-transform: uppercase; }
        td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
        tr:nth-child(even) { background: #f8fafc; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 2px solid #1e40af; font-size: 8px; color: #94a3b8; }
        .page-info { text-align: right; color: #64748b; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header-bar"></div>
    <h1>{$title}</h1>
    <div class="subtitle">Официальный отчет • {$date}</div>
    {$content}
    <div class="footer">
        <table width="100%">
            <tr>
                <td>© {$year} QweesCore Enterprise</td>
                <td class="page-info">Страница {PAGENO} из {nbpg}</td>
            </tr>
        </table>
    </div>
</body>
</html>
HTML;

        $templates = ['minimal' => $minimal, 'rich' => $rich, 'corporate' => $corporate];
        return $templates[$design] ?? $minimal;
    }

    /**
     * Скачать PDF
     */
    private function downloadPdf(string $html, string $filename, string $title = ''): void
    {
        if ($title) {
            $this->mpdf->SetTitle($title);
        }
        $this->mpdf->SetAuthor('QweesCore Admin');
        $this->mpdf->WriteHTML($html);
        $this->mpdf->Output($filename, 'D');
    }
}
