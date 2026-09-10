## Работа с кассовыми ссылками

Кассовая ссылка — ссылка для оплаты в офлайн-сценариях. Вы можете создать кассовую ссылку и распечатать [платежную табличку](https://yookassa.ru/developers/offline-payments/getting-started/basics) с QR-кодом, по которому покупатель сможет оплатить заказ.

С помощью SDK вы можете создать кассовую ссылку, получить информацию о ней, активировать и деактивировать ее, а также привязать к ней торговую точку.

* [Запрос на создание кассовой ссылки](#Запрос-на-создание-кассовой-ссылки)
* [Получить информацию о кассовой ссылке](#Получить-информацию-о-кассовой-ссылке)
* [Активировать кассовую ссылку](#Активировать-кассовую-ссылку)
* [Деактивировать кассовую ссылку](#Деактивировать-кассовую-ссылку)
* [Привязать торговую точку к кассовой ссылке](#Привязать-торговую-точку-к-кассовой-ссылке)

---

### Запрос на создание кассовой ссылки <a name="Запрос-на-создание-кассовой-ссылки"></a>

[Создание кассовой ссылки в документации](https://yookassa.ru/developers/api?codeLang=php#create_pos_link)

Используйте этот запрос, чтобы создать в ЮKassa [объект кассовой ссылки](https://yookassa.ru/developers/api?codeLang=php#pos_link_object) и активировать кассовую ссылку для последующего приема платежей по платежным табличкам.

В ответ на запрос придет объект кассовой ссылки — `PosLinkInfo` — в актуальном статусе.

```php
require_once 'vendor/autoload.php';

$client = new \YooKassa\Client();
$client->setAuth('xxxxxx', 'test_XXXXXXX');

try {
    $posLinkData = [
        'recipient' => [
            'gateway_id' => '123',
        ],
        'pos_link_data' => [
            'link' => 'https://shop.example.ru/pay/1234567890',
        ],
    ];

    $idempotenceKey = uniqid('', true);
    $response = $client->createPosLink($posLinkData, $idempotenceKey);
    if ($response->getStatus() === \YooKassa\Model\PosLink\PosLinkStatus::ACTIVE) {
        // Кассовая ссылка успешно создана
    }
    var_dump($response->toArray());
} catch (\Exception $e) {
    var_dump($e);
}
```
---

### Получить информацию о кассовой ссылке <a name="Получить-информацию-о-кассовой-ссылке"></a>

[Информация о кассовой ссылке в документации](https://yookassa.ru/developers/api?codeLang=php#get_pos_link)

Запрос позволяет получить информацию о текущем состоянии кассовой ссылки по ее уникальному идентификатору.

В ответ на запрос придет объект кассовой ссылки — `PosLinkInfo` — в актуальном статусе.

```php
require_once 'vendor/autoload.php';

$client = new \YooKassa\Client();
$client->setAuth('xxxxxx', 'test_XXXXXXX');

try {
    $posLinkId = 'pl-285d3ab7-0003-5000-9000-0e1166498fda';
    $response = $client->getPosLinkInfo($posLinkId);
    var_dump($response->toArray());
} catch (\Exception $e) {
    var_dump($e);
}
```
---

### Активировать кассовую ссылку <a name="Активировать-кассовую-ссылку"></a>

[Активация кассовой ссылки в документации](https://yookassa.ru/developers/api?codeLang=php#activate_pos_link)

Запрос позволяет активировать ранее деактивированную кассовую ссылку, чтобы она стала доступна для приема платежей.

В ответ на запрос придет объект кассовой ссылки — `PosLinkInfo` — в актуальном статусе.

```php
require_once 'vendor/autoload.php';

$client = new \YooKassa\Client();
$client->setAuth('xxxxxx', 'test_XXXXXXX');

try {
    $posLinkId = 'pl-285d3ab7-0003-5000-9000-0e1166498fda';
    $response = $client->activatePosLink($posLinkId, uniqid('', true));
    if ($response->getStatus() === \YooKassa\Model\PosLink\PosLinkStatus::ACTIVE) {
        // Кассовая ссылка активирована
    }
    var_dump($response->toArray());
} catch (\Exception $e) {
    var_dump($e);
}
```
---

### Деактивировать кассовую ссылку <a name="Деактивировать-кассовую-ссылку"></a>

[Деактивация кассовой ссылки в документации](https://yookassa.ru/developers/api?codeLang=php#deactivate_pos_link)

Запрос позволяет деактивировать кассовую ссылку, прием платежей по ней будет недоступен.

В ответ на запрос придет объект кассовой ссылки — `PosLinkInfo` — в актуальном статусе.

```php
require_once 'vendor/autoload.php';

$client = new \YooKassa\Client();
$client->setAuth('xxxxxx', 'test_XXXXXXX');

try {
    $posLinkId = 'pl-285d3ab7-0003-5000-9000-0e1166498fda';
    $response = $client->deactivatePosLink($posLinkId, uniqid('', true));
    if ($response->getStatus() === \YooKassa\Model\PosLink\PosLinkStatus::INACTIVE) {
        // Кассовая ссылка деактивирована
    }
    var_dump($response->toArray());
} catch (\Exception $e) {
    var_dump($e);
}
```
---

### Привязать торговую точку к кассовой ссылке <a name="Привязать-торговую-точку-к-кассовой-ссылке"></a>

[Изменение торговой точки, привязанной к кассовой ссылке, в документации](https://yookassa.ru/developers/api?codeLang=php#recipient_pos_link)

Запрос позволяет привязать к кассовой ссылке другую торговую точку. В параметре `recipient.gateway_id` передается идентификатор торговой точки, которую вы хотите привязать.

В ответ на запрос придет объект кассовой ссылки — `PosLinkInfo` — в актуальном статусе.

```php
require_once 'vendor/autoload.php';

$client = new \YooKassa\Client();
$client->setAuth('xxxxxx', 'test_XXXXXXX');

try {
    $posLinkId = 'pl-285d3ab7-0003-5000-9000-0e1166498fda';
    $recipientData = [
        'recipient' => [
            'gateway_id' => '456',
        ],
    ];
    $response = $client->recipientPosLink($posLinkId, $recipientData, uniqid('', true));
    var_dump($response->toArray());
} catch (\Exception $e) {
    var_dump($e);
}
```
