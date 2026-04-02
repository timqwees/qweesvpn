<?php declare(strict_types=1);
namespace setting\route\function\Controllers\Admin\Price;

use App\Config\Database;

class Price
{
    /**
     * Получает или изменяет цены тарифов
     * @param string $type Тип операции ('' для получения, 'edit' для изменения)
     * @param array $change Массив изменений для типа 'edit'
     * @return array Массив цен или результат операции
     */
    public static function isPrice(string $type = '', array $change = []): array
    {
        $db = Database::send('SELECT * FROM qwees_price');
        
        if (!is_array($db) || empty($db)) {
            return [];
        }
        
        $priceData = $db[0] ?? [];
        $result = [];
        
        if ($type !== 'edit') {
            // Возвращаем все цены
            foreach ($priceData as $name => $amount) {
                $result[$name] = $amount !== false ? $amount : 0;
            }
            return $result;
        }
        
        // Режим редактирования цен
        foreach ($change as $name => $amount) {
            if (isset($priceData[$name])) {
                if ($amount !== false) {
                    $updateResult = Database::send("UPDATE qwees_price SET $name = ?", [intval($amount)]);
                    
                    if ($updateResult) {
                        file_put_contents(
                            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
                            sprintf(
                                "[%s] [ИЗМЕНЕНИЕ ЦЕН - SUCCESS] Цена тарифа '$name' изменена на >> $amount ₽\n",
                                date('Y-m-d H:i:s')
                            ),
                            FILE_APPEND
                        );
                    }
                }
            }
        }
        
        // Возвращаем обновленные цены
        return self::isPrice();
    }
}