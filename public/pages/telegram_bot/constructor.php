<?php

// Правильный путь к автозагрузчику от корня проекта
$autoloadPath = dirname(__DIR__, 3) . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
  die("Ошибка: Файл автозагрузчика не найден по пути: $autoloadPath\n");
}
require_once $autoloadPath;

// Проверка загрузки классов Nutgram
if (!class_exists('SergiX44\Nutgram\Nutgram')) {
  die("Ошибка: Класс Nutgram не найден. Проверьте установку composer.\n");
}

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

$bot = new Nutgram('8108822256:AAHvWDOWSfezgrcDWXSO19_aydbGdgBHoSc');
global $provider_token;
$provider_token = '381764678:TEST:165097';
$webhook_url = 'https://www.coravpn.ru/webhook/tg_bot';

// Простое управление webhook через аргументы командной строки или GET параметры
if (php_sapi_name() === 'cli') {
  // CLI режим - берем из аргументов
  $action = $argv[1] ?? 'run';
} else {
  // Web режим - берем из GET параметров
  $action = $_GET['action'] ?? 'run';
}

switch ($action) {
  case 'set_webhook':
    try {
      $bot->setWebhook($webhook_url);
      echo "✅ Webhook установлен: $webhook_url\n";
    } catch (Exception $e) {
      echo "❌ Ошибка: " . $e->getMessage() . "\n";
    }
    break;

  case 'delete_webhook':
    try {
      $bot->deleteWebhook();
      echo "✅ Webhook удален\n";
    } catch (Exception $e) {
      echo "❌ Ошибка: " . $e->getMessage() . "\n";
    }
    break;

  case 'check_webhook':
    try {
      $info = $bot->getWebhookInfo();
      echo "📋 Информация о webhook:\n";
      echo "URL: " . ($info->url ?: 'не установлен') . "\n";
      echo "Статус: " . ($info->url ? 'активен' : 'неактивен') . "\n";
      echo "Ошибок: " . $info->last_error_message ?: 'нет' . "\n";
    } catch (Exception $e) {
      echo "❌ Ошибка: " . $e->getMessage() . "\n";
    }
    break;

  case 'run':
  default:
    // Для webhook режима не запускаем $bot->run(), а обрабатываем входящие данные
    if (php_sapi_name() === 'cli') {
      // CLI режим - только управление webhook
      echo "CLI режим. Используйте:\n";
      echo "php public/pages/telegram_bot/constructor.php set_webhook\n";
      echo "php public/pages/telegram_bot/constructor.php check_webhook\n";
      echo "php public/pages/telegram_bot/constructor.php delete_webhook\n";
      exit(0);
    } else {
      // Web режим - webhook обработка
      try {
        registerBotHandlers($bot);

        // Логируем начало обработки webhook
        file_put_contents(
          $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
          sprintf("[%s] [WEBHOOK] Начало обработки webhook запроса\n", date('Y-m-d H:i:s')),
          FILE_APPEND
        );

        // Получаем входящие данные от Telegram
        $input = file_get_contents('php://input');

        // Логируем дополнительную отладочную информацию
        file_put_contents(
          $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
          sprintf(
            "[%s] [WEBHOOK] DEBUG: REQUEST_METHOD=%s, CONTENT_TYPE=%s, CONTENT_LENGTH=%s\n",
            date('Y-m-d H:i:s'),
            $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            $_SERVER['CONTENT_TYPE'] ?? 'unknown',
            $_SERVER['CONTENT_LENGTH'] ?? 'unknown'
          ),
          FILE_APPEND
        );

        if ($input) {
          // Логируем входящий запрос для отладки
          file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
            sprintf("[%s] [WEBHOOK] Получен запрос (длина %d): %s\n", date('Y-m-d H:i:s'), strlen($input), $input),
            FILE_APPEND
          );

          $update = json_decode($input, true);
          if ($update) {
            // Создаем объект Update из массива через статический метод
            $updateObject = \SergiX44\Nutgram\Telegram\Types\Common\Update::fromArray($update);
            // Обрабатываем обновление
            $bot->processUpdate($updateObject);
          } else {
            file_put_contents(
              $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
              sprintf("[%s] [WEBHOOK] Ошибка JSON: %s\n", date('Y-m-d H:i:s'), json_last_error_msg()),
              FILE_APPEND
            );
          }
        } else {
          file_put_contents(
            $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
            sprintf("[%s] [WEBHOOK] Пустой запрос. RAW POST: %s\n", date('Y-m-d H:i:s'), print_r($_POST, true)),
            FILE_APPEND
          );
        }

        // Отправляем ответ 200 OK
        http_response_code(200);
        echo 'OK';
      } catch (Exception $e) {
        // Логируем критическую ошибку
        file_put_contents(
          $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
          sprintf("[%s] [WEBHOOK ERROR] Критическая ошибка: %s\n", date('Y-m-d H:i:s'), $e->getMessage()),
          FILE_APPEND
        );

        http_response_code(500);
        echo 'ERROR';
      }
    }
    break;
}

// =============================================================================
// ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
// =============================================================================

$data = [
  'faq' => 'FAQ',
  'partner' => 'Стать партнером',
  'vpn' => 'Открыть VPN'
];

$domain = 'www.coravpn.ru';

function getMainKeyboard(): ReplyKeyboardMarkup
{
  global $data;

  return ReplyKeyboardMarkup::make(resize_keyboard: false)
    ->addRow(
      KeyboardButton::make($data['vpn']),
      KeyboardButton::make($data['partner'])
    )
    ->addRow(KeyboardButton::make($data['faq']));
}

function sendWithMainMenu(Nutgram $bot, string $text): void
{
  global $data;

  $bot->sendMessage(
    text: $text,
    parse_mode: 'HTML',
    reply_markup: getMainKeyboard()
  );
}

function sendSimple(Nutgram $bot, string $text, string $parse = 'HTML'): void
{
  $bot->sendMessage(text: $text, parse_mode: $parse);
}

function sendInline(Nutgram $bot, string $text, InlineKeyboardMarkup $keyboard, string $parse = 'HTML'): void
{
  $bot->sendMessage(text: $text, parse_mode: $parse, reply_markup: $keyboard);
}

function setBtn(string $text, string $key)
{
  return InlineKeyboardButton::make($text, callback_data: $key);
}

function setBtnUrl(string $text, string $url)
{
  return InlineKeyboardButton::make($text, url: $url);
}

function supportBtn(): InlineKeyboardButton
{
  return InlineKeyboardButton::make('💬 Связаться с оператором', url: 'https://t.me/spcoravpn_bot');
}

function sendTestCard(Nutgram $bot): void
{
  sendSimple(
    $bot,
    "Тестовая карта:\n<code>1111 1111 1111 1026</code>\nСрок: любой будущий\nCVC: <code>000</code>"
  );
}

function getAllFaq(): array
{
  return [
    'install' => [
      'title' => '1️⃣ Установка и подключение',
      'questions' => [
        'install_1' => [
          'q' => 'Как установить Cora VPN?',
          'a' => "👇 Просто:\nСкачай приложение для своей платформы — <a href=\"https://coravpn.ru/download\">ссылка на скачивание</a>\nУстанови и открой.\nВведи свой логин и пароль.\nНажми “Подключить”.\nВсё! VPN активен 🚀"
        ],
        'install_2' => [
          'q' => 'У вас есть приложение для iPhone / Android / Windows / macOS?',
          'a' => "Да! 📱💻\nCora VPN доступен для:\n• iPhone / iPad (iOS)\n• Android\n• Windows\n• macOS\nСсылки на установку — <a href=\"https://coravpn.ru/download\">тут</a>"
        ],
        'install_3' => [
          'q' => 'Как подключиться к серверу?',
          'a' => "🔌 Открой приложение → выбери любую страну → нажми “Подключить”.\nЧерез пару секунд VPN активен ✅\nСовет: выбери ближайший сервер для максимальной скорости ⚡️"
        ],
        'install_4' => [
          'q' => 'VPN не подключается / пишет ошибку — что делать?',
          'a' => "Попробуй:\n1️⃣ Перезапусти приложение.\n2️⃣ Выбери другой сервер.\n3️⃣ Проверь интернет.\n4️⃣ Отключи другие VPN-приложения.\nЕсли не помогло — нажми “Связаться с оператором” 💬"
        ],
        'install_5' => [
          'q' => 'Интернет не работает после включения VPN — как исправить?',
          'a' => "🛠 Попробуй:\nВыключить и снова включить VPN.\nВыбрать другой сервер.\nПерезапустить устройство.\nЕсли интернет по-прежнему не работает — напиши нам, поможем 💪"
        ],
        'install_6' => [
          'q' => 'Почему скорость стала ниже?',
          'a' => "🌍 Скорость зависит от загруженности сервера и расстояния.\nПопробуй:\n🔹 Выбрать сервер ближе к твоей стране.\n🔹 Закрыть другие программы, использующие интернет.\nCora VPN всегда ищет самый быстрый маршрут ⚡️"
        ],
        'install_7' => [
          'q' => 'Как выбрать самый быстрый сервер?',
          'a' => "🚀 В приложении есть метка “Быстрый сервер” — выбирай её.\nИли ориентируйся по пингам — чем меньше значение, тем выше скорость 📶"
        ],
        'install_8' => [
          'q' => 'Можно ли включать VPN на нескольких устройствах одновременно?',
          'a' => "Да ✅\nОдин аккаунт поддерживает до 5 устройств (например, телефон + ноутбук + планшет)."
        ],
        'install_9' => [
          'q' => 'Работает ли VPN с Wi-Fi в общественных местах?',
          'a' => "Конечно 💪\nВ общественном Wi-Fi VPN особенно нужен: он шифрует весь трафик и защищает твои данные от перехвата 🔒"
        ],
        'install_10' => [
          'q' => 'Почему сайты всё ещё заблокированы, хотя VPN включен?',
          'a' => "Такое бывает редко.\nПопробуй:\n1️⃣ Переключиться на другой сервер.\n2️⃣ Очистить кэш браузера.\n3️⃣ Перезапустить приложение.\nЕсли блокировки не уходят — нажми “Связаться с оператором”, решим 🚀"
        ],
      ]
    ],
    'payment' => [
      'title' => '2️⃣ Оплата и подписка',
      'questions' => [
        'payment_1' => [
          'q' => 'Как оплатить подписку?',
          'a' => "💳 Всё просто:\nПерейди по ссылке 👉 <a href=\"https://coravpn.ru/pay\">ссылка на оплату</a>\nВыбери удобный способ (карта, СБП, крипта).\nПосле оплаты активируй подписку в приложении.\nГотово! Cora VPN будет работать сразу 🚀"
        ],
        'payment_2' => [
          'q' => 'Какие способы оплаты доступны?',
          'a' => "Мы поддерживаем все популярные способы 💰:\n💳 Банковские карты (Visa, Mastercard, МИР)\n💸 СБП\n₿ Криптовалюта (по запросу)\n💵 Электронные кошельки\nВсё работает быстро и безопасно 🔒"
        ],
        'payment_3' => [
          'q' => 'У меня не проходит оплата — что делать?',
          'a' => "Попробуй:\n1️⃣ Отключить VPN при оплате.\n2️⃣ Проверить баланс карты.\n3️⃣ Использовать другой способ (например, СБП).\nЕсли всё равно не получается — нажми “Связаться с оператором”, поможем 💬"
        ],
        'payment_4' => [
          'q' => 'Как отменить подписку?',
          'a' => "😌 Просто напиши “Отменить подписку” — и мы остановим автопродление.\nТвоя текущая подписка останется активной до конца оплаченного периода."
        ],
        'payment_5' => [
          'q' => 'Как продлить подписку?',
          'a' => "🔁 После окончания срока можно продлить:\n— вручную через <a href=\"https://coravpn.ru/pay\">ссылку на оплату</a>\n— или автоматически (если включено автопродление).\nХочешь напоминание? Нажми “Напомнить о продлении” 🔔"
        ],
        'payment_6' => [
          'q' => 'Можно ли получить чек или квитанцию об оплате?',
          'a' => "Конечно! 💼 Напиши нам номер заказа или почту, с которой оформлял оплату — вышлем чек в ответ."
        ],
        'payment_7' => [
          'q' => 'Есть ли бесплатный пробный период?',
          'a' => "🎁 Да! Мы даём бесплатный тест-доступ, чтобы попробовать Cora VPN без рисков.\nНажми “Получить тест-доступ” 👇 и активируй свой пробный период."
        ],
        'payment_8' => [
          'q' => 'Как активировать промокод или скидку?',
          'a' => "💸 При оплате введи свой промокод в специальное поле — скидка применится автоматически.\nЕсли возникнут сложности — напиши код оператору, активируем вручную 😉"
        ],
      ]
    ],
    'account' => [
      'title' => '3️⃣ Аккаунт и вход',
      'questions' => [
        'account_1' => [
          'q' => 'Как войти в аккаунт?',
          'a' => "🔐 Просто:\nОткрой приложение Cora VPN.\nВведи логин (почту) и пароль.\nНажми «Войти».\nЕсли ты ещё не зарегистрирован — нажми «Создать аккаунт» прямо в приложении."
        ],
        'account_2' => [
          'q' => 'Забыл логин / пароль — как восстановить доступ?',
          'a' => "Не переживай 😌\n1️⃣ Нажми «Забыли пароль?» в приложении или на сайте.\n2️⃣ Укажи почту, к которой привязан аккаунт.\n3️⃣ Получи письмо с ссылкой для восстановления.\nЕсли письмо не пришло — проверь спам или напиши нам 💬"
        ],
        'account_3' => [
          'q' => 'Можно ли сменить почту или логин?',
          'a' => "Да ✉️ Просто напиши в поддержку: «Хочу сменить почту с [старый email] на [новый email]»\nМы проверим данные и обновим аккаунт вручную 🔄"
        ],
        'account_4' => [
          'q' => 'Как удалить аккаунт?',
          'a' => "😔 Если хочешь удалить аккаунт навсегда — напиши “Удалить аккаунт”, и бот передаст запрос оператору.\nПосле удаления все данные и подписка будут безвозвратно удалены ⚠️"
        ],
        'account_5' => [
          'q' => 'Как передать подписку другому человеку?',
          'a' => "🔁 Подписка привязана к аккаунту, поэтому напрямую передать её нельзя.\nНо ты можешь оформить подарочный доступ — напиши нам, и мы создадим персональный промокод 🎁"
        ],
      ]
    ],
    'vpn' => [
      'title' => '4️⃣ Работа VPN',
      'questions' => [
        'vpn_1' => [
          'q' => 'Работает ли VPN для Netflix / YouTube / Telegram / TikTok?',
          'a' => "Да ✅ Cora VPN стабильно открывает Netflix, YouTube, Telegram, TikTok и другие платформы без ограничений.\nПросто выбери подходящий сервер — и наслаждайся контентом без блокировок 🚀"
        ],
        'vpn_2' => [
          'q' => 'Можно ли выбрать страну вручную?',
          'a' => "Конечно 🌎 В приложении просто нажми на список серверов и выбери нужную страну вручную.\nСовет: чем ближе страна — тем выше скорость ⚡️"
        ],
        'vpn_3' => [
          'q' => 'Какие страны есть в списке серверов?',
          'a' => "🇩🇪 Германия 🇳🇱 Нидерланды 🇵🇱 Польша 🇫🇮 Финляндия 🇸🇪 Швеция 🇨🇦 Канада 🇸🇬 Сингапур 🇯🇵 Япония 🇺🇸 США и другие — список постоянно обновляется 💫"
        ],
        'vpn_4' => [
          'q' => 'Сохраняете ли вы логи пользователей?',
          'a' => "❌ Нет. Cora VPN придерживается политики нулевых логов (No-Logs Policy).\nМы не храним историю посещений, IP, переписку или личные данные. Только ты контролируешь свой интернет 🔒"
        ],
        'vpn_5' => [
          'q' => 'Это безопасно? Вы видите, что я делаю в интернете?',
          'a' => "🔐 Абсолютно безопасно. Мы не видим и не отслеживаем твои действия.\nВсе данные проходят через зашифрованный туннель, который невозможно перехватить. Твоя анонимность — наш главный приоритет 💪"
        ],
        'vpn_6' => [
          'q' => 'Работает ли VPN в других странах (например, за границей)?',
          'a' => "Да 🌍 Cora VPN работает по всему миру — включай в любой точке планеты.\nЕсли какая-то страна блокирует VPN, мы используем обходные технологии, чтобы соединение оставалось стабильным 💫"
        ],
        'vpn_7' => [
          'q' => 'Что делать, если соединение часто обрывается?',
          'a' => "📶 Попробуй:\n1️⃣ Переключиться на другой сервер.\n2️⃣ Перезапустить приложение.\n3️⃣ Проверить стабильность интернета.\nЕсли не помогает — нажми «Связаться с оператором», и мы решим проблему лично 💬"
        ],
      ]
    ],
    'advantages' => [
      'title' => '5️⃣ Чем Cora VPN лучше других сервисов?',
      'questions' => [
        'adv_1' => [
          'q' => 'Ключевые преимущества Cora VPN',
          'a' => "🎯 Вот наши ключевые преимущества:\n— Максимальная скорость и оптимизированные сервера.\n— Полная защита данных и строгая политика «без логов».\n— Простое приложение, понятное даже новичкам.\n— Поддержка 24/7 и реальные сервера по миру.\nМы не просто как «ещё один VPN» — мы делаем так, чтобы ты начал пользоваться и сразу понял, что это работает."
        ],
        'adv_2' => [
          'q' => 'Есть ли реферальная программа или бонус за друга?',
          'a' => "✅ Да! Приглашай друга — он оформляет подписку, ты получаешь бонус или скидку.\n👉 Нажми «Реферальная программа» в меню, получи свою ссылку-код и делись."
        ],
        'adv_3' => [
          'q' => 'Есть ли круглосуточная поддержка?',
          'a' => "🔔 Да — поддержка работает 24 ч / 7 дней.\nЕсли что-то не получается — пиши «Связаться с оператором» и мы откликнемся как можно скорее."
        ],
        'adv_4' => [
          'q' => 'Где можно скачать актуальное приложение?',
          'a' => "📲 Всё просто:\n— Скачай из официального магазина (App Store или Google Play).\n— Или на нашем сайте <a href=\"https://coravpn.ru/download\">ссылка на загрузку</a>.\nУбедись, что версия актуальна — обновления могут содержать важные улучшения."
        ],
        'adv_5' => [
          'q' => 'Как понять, что VPN включён и работает?',
          'a' => "✅ Проверь следующее:\n— В приложении видно статус «Подключено».\n— Поменялась геолокация (если выбрал сервер другой страны).\n— Попробуй зайти на заблокированный сайт — если открывается, значит работает.\n— Значок VPN появляется в строке состояния устройства.\nЕсли что-то не так — переподключись или смени сервер."
        ],
      ]
    ],
  ];
}

// =============================================================================
// ФУНКЦИЯ РЕГИСТРАЦИИ ОБРАБОТЧИКОВ
// =============================================================================

function registerBotHandlers(Nutgram $bot): void
{
  global $data, $domain, $provider_token;

  // Проверяем что $data доступна
  if (!isset($data) || !is_array($data)) {
    $data = [
      'faq' => 'FAQ',
      'partner' => 'Стать партнером',
      'vpn' => 'Открыть VPN'
    ];
  }

  if (!isset($domain)) {
    $domain = 'www.coravpn.ru';
  }

  $bot->onCommand('start', function (Nutgram $bot) {
    global $data, $domain;

    // Логируем обработку команды
    file_put_contents(
      $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
      sprintf(
        "[%s] [WEBHOOK] Обработка команды /start от пользователя %s\n",
        date('Y-m-d H:i:s'),
        $bot->user()?->id ?? 'unknown'
      ),
      FILE_APPEND
    );

    $name = $bot->user()?->first_name ?? 'Друг';

    $inlineKeyboard = InlineKeyboardMarkup::make()
      ->addRow(
        InlineKeyboardButton::make($data['vpn'], url: "https://$domain"),
        InlineKeyboardButton::make($data['partner'], callback_data: 'partner_info')
      )
      ->addRow(InlineKeyboardButton::make($data['faq'], callback_data: 'faq_main'));

    $bot->sendVideo(
      video: "https://$domain/assets/video/1.mp4",
      caption: "Привет, $name! 👋\n🚨 Первые 7 дней - безлимитного VPN,\nабсолютно бесплатно, без отключения!\n\n*Такого еще не было 😍 ‼️*\n\n_Нажми и пользуйся!_",
      parse_mode: 'Markdown',
      reply_markup: $inlineKeyboard
    );
  });

  $bot->onText($data['vpn'], function (Nutgram $bot) {
    global $data, $domain;

    sendInline($bot, '🚀 Нажми на кнопку ниже, чтобы открыть приложение:', InlineKeyboardMarkup::make()->addRow(
      InlineKeyboardButton::make('Открыть', url: "https://$domain")
    ));
  });

  $bot->onText($data['partner'], function (Nutgram $bot) {
    global $data;

    $name = $bot->user()?->first_name ?? 'Друг';

    sendSimple($bot, "$name, для тебя подготовили выгодные тарифы!\n\nТариф: <b>Free</b>\nЦена: <s>150 ₽</s> 60 ₽/взнос\nКомисия: 20%\n10 клиентов\n\nТариф: <b>Start</b>\nЦена: <i>999 ₽/мес</i>\nКомисия: 40%\nБез лимитов\n\nТариф: <b>Pro</b>\nЦена: <i>2 990 ₽/мес</i>\nКомисия: 60%\nWhite-label, API, приоритетные выплаты", 'Markdown');
    sendInline(
      $bot,
      '💰 Выберите подходящий тариф:',
      InlineKeyboardMarkup::make()
        ->addRow(InlineKeyboardButton::make('Тариф Free — 60 ₽/взнос', callback_data: 'pay_free'))
        ->addRow(InlineKeyboardButton::make('Тариф Start — 999 ₽/мес', callback_data: 'pay_start'))
        ->addRow(InlineKeyboardButton::make('Тариф Pro — 2 990 ₽/мес', callback_data: 'pay_pro'))
    );
    // sendTestCard($bot);
  });

  $bot->onText($data['faq'], function (Nutgram $bot) {
    global $data;

    $faq = getAllFaq();
    $kb = InlineKeyboardMarkup::make();
    foreach ($faq as $key => $cat) {
      $kb->addRow(InlineKeyboardButton::make($cat['title'], callback_data: 'faq_cat_' . $key));
    }
    sendInline($bot, "<b>❓ {$data['faq']}</b>\n\nВыберите раздел:", $kb);
  });

  // =============================================================================
  // CALLBACK
  // =============================================================================

  $bot->onCallbackQuery(function (Nutgram $bot) {
    global $data, $domain, $provider_token;

    $callbackData = $bot->callbackQuery()->data ?? '';

    // Логируем все callback запросы для отладки
    file_put_contents(
      $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
      sprintf(
        "[%s] [CALLBACK] Получен callback: data=%s, пользователь=%s\n",
        date('Y-m-d H:i:s'),
        $callbackData,
        $bot->user()?->id ?? 'unknown'
      ),
      FILE_APPEND
    );

    // Оплата тарифов
    if (str_starts_with($callbackData, 'pay_')) {
      $plan = substr($callbackData, 4);
      $plans = [
        'free' => ['title' => 'Тариф Партнер Free (CVPN)', 'desc' => '10 клиентов, 20% комиссии', 'amount' => 9900],
        'start' => ['title' => 'Тариф Партнер Start (CVPN)', 'desc' => 'Без лимитов, 40% комиссии', 'amount' => 99900],
        'pro' => ['title' => 'Тариф Партнер Pro (CVPN)', 'desc' => 'White-label, API, 60% комиссии', 'amount' => 299000],
      ];
      $p = $plans[$plan] ?? $plans['free'];

      $prices = [['label' => $p['title'], 'amount' => $p['amount']]];

      // Логируем перед отправкой инвойса
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [PAYMENT] Подготовка инвойса: plan=%s, title=%s, amount=%d, provider_token=%s\n",
          date('Y-m-d H:i:s'),
          $plan,
          $p['title'],
          $p['amount'],
          $provider_token ?? 'NOT_SET'
        ),
        FILE_APPEND
      );

      try {
        // Логируем попытку отправки инвойса
        file_put_contents(
          $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
          sprintf(
            "[%s] [PAYMENT] Попытка отправки инвойса: план=%s, сумма=%d, provider_token=%s, пользователь=%s\n",
            date('Y-m-d H:i:s'),
            $plan,
            $p['amount'],
            $provider_token ?? 'NOT_SET',
            $bot->user()?->id ?? 'unknown'
          ),
          FILE_APPEND
        );

        $bot->sendInvoice(
          chat_id: $bot->user()->id,
          title: $p['title'],
          description: $p['desc'],
          payload: $plan . '_plan_' . time(),
          provider_token: $provider_token,
          currency: 'RUB',
          prices: $prices
        );

        // Логируем успешную отправку инвойса
        file_put_contents(
          $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
          sprintf(
            "[%s] [PAYMENT] Оплата bot отправлен: план=%s, сумма=%d, пользователь=%s\n",
            date('Y-m-d H:i:s'),
            $plan,
            $p['amount'],
            $bot->user()?->id ?? 'unknown'
          ),
          FILE_APPEND
        );
      } catch (Exception $e) {
        // Логируем ошибку отправки инвойса
        file_put_contents(
          $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
          sprintf(
            "[%s] [PAYMENT ERROR] Ошибка отправки инвойса: %s, план=%s, пользователь=%s\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $plan,
            $bot->user()?->id ?? 'unknown'
          ),
          FILE_APPEND
        );

        sendSimple($bot, "❌ Произошла ошибка при создании платежа. Попробуйте позже или свяжитесь с поддержкой. telegrambot");
      }

      // Отвечаем на callback
      $bot->answerCallbackQuery();
      return;
    }

    // Подтверждение тарифа
    if (str_starts_with($callbackData, 'confirm_')) {
      $plan = substr($callbackData, 8);
      $plans = [
        'free' => ['title' => 'Тариф Free', 'desc' => '10 клиентов, 20% комиссии', 'amount' => 9900],
        'start' => ['title' => 'Тариф Start', 'desc' => 'Без лимитов, 40% комиссии', 'amount' => 99900],
        'pro' => ['title' => 'Тариф Pro', 'desc' => 'White-label, API, 60% комиссии', 'amount' => 299000],
      ];
      $p = $plans[$plan] ?? $plans['free'];

      sendInline($bot, "Тариф: {$p['title']}\nСтоимость: " . ($p['amount'] / 100) . " ₽\n\nПожалуйста, подтвердите ваши действия!", InlineKeyboardMarkup::make()
        ->addRow(setBtn('Оплатить', 'pay_' . $plan))
        ->addRow(setBtn('Вернуться', 'partner_info')));

      // Отвечаем на callback
      $bot->answerCallbackQuery();
      return;
    }

    // Информация о партнерстве
    if ($callbackData === 'partner_info') {
      $name = $bot->user()?->first_name ?? 'Друг';

      $bot->sendMessage(
        text: "$name, для тебя подготовили выгодные тарифы!\n\nТариф: <b>Free</b>\nЦена: <s>150 ₽</s> 99 ₽/взнос\nКомисия: 20%\n10 клиентов\n\nТариф: <b>Start</b>\nЦена: <i>999 ₽/мес</i>\nКомисия: 40%\nБез лимитов\n\nТариф: <b>Pro</b>\nЦена: <i>2 990 ₽/мес</i>\nКомисия: 60%\nWhite-label, API, приоритетные выплаты",
        parse_mode: 'HTML'
      );

      sendInline($bot, '💰 Выберите подходящий тариф:', InlineKeyboardMarkup::make()
        ->addRow(setBtn('Тариф Free — 99 ₽/взнос', 'confirm_free'))
        ->addRow(setBtn('Тариф Start — 999 ₽/мес', 'confirm_start'))
        ->addRow(setBtn('Тариф Pro — 2 990 ₽/мес', 'confirm_pro'))
        ->addRow(setBtn('← Назад', 'start')));

      // Отвечаем на callback
      $bot->answerCallbackQuery();
      return;
    }

    $faq = getAllFaq();

    // Возврат к главному меню
    if ($callbackData === 'start') {
      global $data, $domain;
      $name = $bot->user()?->first_name ?? 'Друг';

      $inlineKeyboard = InlineKeyboardMarkup::make()
        ->addRow(
          InlineKeyboardButton::make($data['vpn'], url: "https://$domain"),
          InlineKeyboardButton::make($data['partner'], callback_data: 'partner_info')
        )
        ->addRow(InlineKeyboardButton::make($data['faq'], callback_data: 'faq_main'));

      $bot->sendVideo(
        video: "https://$domain/assets/video/1.mp4",
        caption: "Привет, $name!\n🚨 Первые 7 дней - безлимитного VPN,\nабсолютно бесплатно, без отключения!\n\n*Такого еще не было 😍 ‼️*\n\n_Нажми и пользуйся!_",
        parse_mode: 'Markdown',
        reply_markup: $inlineKeyboard
      );

      // Отвечаем на callback
      $bot->answerCallbackQuery();
      return;
    }

    // Главное меню FAQ
    if ($callbackData === 'faq_main') {
      $kb = InlineKeyboardMarkup::make();
      foreach ($faq as $key => $cat) {
        $kb->addRow(InlineKeyboardButton::make($cat['title'], callback_data: 'faq_cat_' . $key));
      }
      $kb->addRow(setBtn('← Назад', 'start'));
      sendInline($bot, "<b>❓ {$data['faq']}</b>\n\nВыберите раздел:", $kb);

      // Отвечаем на callback
      $bot->answerCallbackQuery();
      return;
    }

    // Раздел → список вопросов
    if (str_starts_with($callbackData, 'faq_cat_')) {
      $catKey = substr($callbackData, 8);
      if (isset($faq[$catKey])) {
        $kb = InlineKeyboardMarkup::make();
        foreach ($faq[$catKey]['questions'] as $qKey => $q) {
          $kb->addRow(InlineKeyboardButton::make($q['q'], callback_data: $qKey));
        }
        $kb->addRow(setBtn('← Назад', 'faq_main'));
        sendInline($bot, "<b>{$faq[$catKey]['title']}</b>\n\nВыберите вопрос:", $kb);
      }

      // Отвечаем на callback
      $bot->answerCallbackQuery();
      return;
    }

    // Ответ на вопрос
    foreach ($faq as $catKey => $cat) {
      if (isset($cat['questions'][$callbackData])) {
        $q = $cat['questions'][$callbackData];
        $kb = InlineKeyboardMarkup::make()
          ->addRow(setBtn('← Назад', 'faq_cat_' . $catKey))
          ->addRow(supportBtn());
        sendInline($bot, "<b>{$q['q']}</b>\n\n{$q['a']}", $kb);

        // Отвечаем на callback
        $bot->answerCallbackQuery();
        return;
      }
    }

    // Если ни один обработчик не сработал, все равно отвечаем на callback
    $bot->answerCallbackQuery();
  });

  // Платежи
  $bot->onPreCheckoutQuery(function (Nutgram $bot) use ($provider_token) {
    try {
      $bot->answerPreCheckoutQuery(ok: true);

      // Логируем успешную проверку платежа
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [PAYMENT] Pre-checkout подтвержден: пользователь=%s\n",
          date('Y-m-d H:i:s'),
          $bot->user()?->id ?? 'unknown'
        ),
        FILE_APPEND
      );
    } catch (Exception $e) {
      // Логируем ошибку
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [PAYMENT ERROR] Ошибка pre-checkout: %s, пользователь=%s\n",
          date('Y-m-d H:i:s'),
          $e->getMessage(),
          $bot->user()?->id ?? 'unknown'
        ),
        FILE_APPEND
      );
    }
  });

  $bot->onSuccessfulPayment(function (Nutgram $bot) use ($provider_token) {
    try {
      $p = $bot->message()->successful_payment;
      $sum = $p->total_amount / 100;

      // Логируем успешную оплату
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [PAYMENT SUCCESS] Оплата прошла успешно: сумма=%d, payload=%s, пользователь=%s\n",
          date('Y-m-d H:i:s'),
          $p->total_amount,
          $p->invoice_payload,
          $bot->user()?->id ?? 'unknown'
        ),
        FILE_APPEND
      );

      sendSimple($bot, "✅ <b>Оплата прошла успешно!</b>\n\nСумма: <b>$sum ₽</b>\nТариф активирован!\nОбратитесь за консультацией к: @maksim1144");
    } catch (Exception $e) {
      // Логируем ошибку
      file_put_contents(
        $_ENV['LOG_FILE_NAME'] ?? 'coravpn.log',
        sprintf(
          "[%s] [PAYMENT ERROR] Ошибка обработки успешной оплаты: %s, пользователь=%s\n",
          date('Y-m-d H:i:s'),
          $e->getMessage(),
          $bot->user()?->id ?? 'unknown'
        ),
        FILE_APPEND
      );

      sendSimple($bot, "❌ Произошла ошибка при обработке платежа. Свяжитесь с поддержкой.");
    }
  });
}
