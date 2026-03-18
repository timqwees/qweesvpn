#!/bin/bash

# Скрипт для расчета ежемесячных доходов партнеров за последние 12 месяцев
# Запускается 1 раз в начале месяца через cron

# Путь к директории проекта
PROJECT_DIR="/var/www/u3296338/data/www/coravpn.ru"

# Лог-файл для cron
LOG_FILE="$PROJECT_DIR/logs/partner_revenue.log"

# Создаем директорию для логов, если не существует
mkdir -p "$(dirname "$LOG_FILE")"

# Записываем время запуска
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Начало расчета доходов партнеров за последние 12 месяцев" >> "$LOG_FILE"

# Переходим в директорию проекта
cd "$PROJECT_DIR"

# Запускаем PHP скрипт для расчета доходов за последние 12 месяцев
php -r "
require_once 'setting/route/function/functions.php';
require_once 'app/config/database.php';
require_once 'app/config/session.php';

// Инициализируем сессию
App\Config\Session::init();

\$currentYear = date('Y');
\$currentMonth = date('n');

// Рассчитываем доходы за последние 12 месяцев
for (\$i = 0; \$i < 12; \$i++) {
    // Вычисляем месяц и год для расчета
    \$timestamp = strtotime(\"-\$i months\", strtotime(\$currentYear . '-' . \$currentMonth . '-01'));
    \$targetYear = date('Y', \$timestamp);
    \$targetMonth = date('n', \$timestamp);
    
    echo \"[$(date '+%Y-%m-%d %H:%M:%S')] Расчет доходов за \$targetYear-\$targetMonth\\n\";
    
    // Рассчитываем и сохраняем доходы за этот месяц
    \$result = Setting\Route\Function\Functions::calculateMonthlyPartnerRevenue(\$targetYear, \$targetMonth);
    
    if (\$result) {
        echo \"[$(date '+%Y-%m-%d %H:%M:%S')] Доходы за \$targetYear-\$targetMonth успешно рассчитаны\\n\";
    } else {
        echo \"[$(date '+%Y-%m-%d %H:%M:%S')] Ошибка при расчете доходов за \$targetYear-\$targetMonth\\n\";
    }
}

echo \"[$(date '+%Y-%m-%d %H:%M:%S')] Расчет доходов за последние 12 месяцев завершен\\n\";
" >> "$LOG_FILE" 2>&1

# Записываем время завершения
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Завершение скрипта расчета доходов партнеров" >> "$LOG_FILE"
echo "----------------------------------------" >> "$LOG_FILE"

# Выводим результат для cron
echo "Partner revenue calculation for last 12 months completed. Check $LOG_FILE for details."
