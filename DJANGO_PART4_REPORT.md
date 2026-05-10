# Сравнительный анализ: QweesCore vs Django 4. Часть 4

## Задание из учебника Django 4. Часть 4

### Требуемый функционал Django:
1. `models.URLField()` — поле для URL с валидацией
2. `__icontains` и `__contains` — поиск по подстроке
3. `values()`, `values_list()` — получение конкретных полей
4. `count()`, `exists()` — агрегатные функции
5. `update()`, `delete()` — массовые операции
6. Использование кеш-фреймворка
7. F expressions — операции с полями БД
8. The Http404 exception

---

## Отчет о наличии/отсутствии функционала

### ⚠️ 1. models.URLField()

**Статус: ЧАСТИЧНО (Ручная валидация)**

**Django-вариант:**
```python
from django.db import models

class Website(models.Model):
    name = models.CharField(max_length=100)
    url = models.URLField(max_length=200)  # Автовалидация URL
    
# Валидация при сохранении:
website = Website(name='Google', url='not-a-url')  # ValidationError!
```

**QweesCore-вариант:**
```php
// app/Controllers/AuthController.php:100
$isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);

// Аналог для URL (нет в коде, но можно добавить):
$isValidUrl = filter_var($url, FILTER_VALIDATE_URL);
```

**Отличия:**
- ❌ Нет ORM-уровня URLField
- ❌ Нет автоматической валидации при сохранении модели
- ✅ Есть `filter_var()` с `FILTER_VALIDATE_URL` для ручной проверки
- ✅ Можно добавить валидацию в метод `save()`

**Пример реализации:**
```php
// В AdminDatabase.php при сохранении:
if (strpos($column, 'url') !== false || strpos($column, 'link') !== false) {
    if (!filter_var($value, FILTER_VALIDATE_URL)) {
        Message::set('error', "Некорректный URL в поле $column");
        return false;
    }
}
```

---

### ✅ 2. __icontains и __contains

**Статус: ЕСТЬ (через SQL LIKE с LOWER)**

**Django-вариант:**
```python
# __contains — case-sensitive
Article.objects.filter(title__contains='django')  # WHERE title LIKE '%django%'

# __icontains — case-insensitive
Article.objects.filter(title__icontains='Django')  # WHERE LOWER(title) LIKE '%django%'
```

**QweesCore-вариант:**
```php
// setting/route/function/Controllers/admin/AdminDatabase.php:205-228
public static function search(string $table, array $columns, string $query, int $limit = 50): array
{
    $conditions = [];
    $params = [];

    foreach ($columns as $col) {
        // Аналог __icontains — case-insensitive
        $conditions[] = "LOWER($col) LIKE ?";
        $params[] = '%' . strtolower($query) . '%';
    }

    $result = Database::send("SELECT * FROM $table WHERE " . implode(' OR ', $conditions) . " LIMIT $limit", $params);
    return is_array($result) ? $result : [];
}
```

**Использование:**
```php
// Поиск по всем колонкам (case-insensitive)
$data = AdminDatabase::search('qwees_users', ['first_name', 'last_name', 'email'], 'tim', 50);
// SQL: WHERE LOWER(first_name) LIKE '%tim%' OR LOWER(last_name) LIKE '%tim%' OR LOWER(email) LIKE '%tim%'
```

**Реализовано:**
- ✅ `__icontains` — через `LOWER(column) LIKE '%value%'`
- ⚠️ `__contains` — можно добавить без `LOWER()` для case-sensitive

---

### ⚠️ 3. values() и values_list()

**Статус: ЧАСТИЧНО (Ручной SQL SELECT)**

**Django-вариант:**
```python
# values() — словари
User.objects.values('username', 'email')  # [{'username': 'tim', 'email': 'a@b.com'}, ...]

# values_list() — кортежи
User.objects.values_list('username', 'email')  # [('tim', 'a@b.com'), ...]

# flat=True — плоский список
User.objects.values_list('username', flat=True)  # ['tim', 'admin', ...]
```

**QweesCore-вариант:**
```php
// Ручной SELECT с указанием колонок
$result = Database::send("SELECT username, email FROM qwees_users");
// Результат: [['username' => 'tim', 'email' => 'a@b.com'], ...]

// values_list аналог:
$result = Database::send("SELECT username FROM qwees_users");
$flatList = array_column($result, 'username');  // ['tim', 'admin', ...]
```

**Отличия:**
- ❌ Нет метода `values()` у моделей
- ❌ Нет метода `values_list()`
- ✅ Можно реализовать через `array_column()` или ручной SQL

**Можно добавить:**
```php
class AdminDatabase {
    public static function values(string $table, array $columns): array {
        $cols = implode(', ', $columns);
        return Database::send("SELECT $cols FROM $table");
    }
    
    public static function valuesList(string $table, string $column): array {
        $result = Database::send("SELECT $column FROM $table");
        return array_column($result, $column);
    }
}
```

---

### ✅ 4. count() и exists()

**Статус: ЕСТЬ (Полная реализация)**

**Django-вариант:**
```python
# count()
User.objects.count()  # SELECT COUNT(*) FROM users
User.objects.filter(status='on').count()  # SELECT COUNT(*) WHERE status='on'

# exists()
User.objects.filter(email='test@test.com').exists()  # True/False
```

**QweesCore-вариант:**
```php
// setting/route/function/Controllers/admin/AdminDatabase.php:164-168
public static function getCount(string $table): int
{
    $result = Database::send("SELECT COUNT(*) as count FROM $table");
    return (is_array($result) && !empty($result)) ? (int) ($result[0]['count'] ?? 0) : 0;
}

// exists() аналог:
public static function exists(string $table, string $column, $value): bool
{
    $result = Database::send("SELECT 1 FROM $table WHERE $column = ? LIMIT 1", [$value]);
    return is_array($result) && !empty($result);
}
```

**Использование:**
```php
// Количество записей в таблице
$count = AdminDatabase::getCount('qwees_users');  // 150

// В шаблоне database.php:96
<span class="text-xs font-semibold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full"><?= $count ?></span>

// Аналог exists() (проверка email в Auth.php):
$existing = Database::send("SELECT id FROM qwees_users WHERE email = ? LIMIT 1", [$userData['email']]);
if (!empty($existing)) {
    return ['success' => false, 'message' => 'Email уже существует'];
}
```

**Реализовано:**
- ✅ `count()` — `AdminDatabase::getCount()`
- ✅ `exists()` — через `SELECT 1 ... LIMIT 1` в `Auth.php`

---

### ✅ 5. update() и delete()

**Статус: ЕСТЬ (Полная реализация)**

**Django-вариант:**
```python
# Массовый update
Article.objects.filter(status='draft').update(status='published')

# Массовый delete
Article.objects.filter(created_at__lt='2023-01-01').delete()

# Одиночный delete
article = Article.objects.get(id=1)
article.delete()
```

**QweesCore-вариант:**
```php
// setting/route/function/Controllers/admin/AdminDatabase.php:237-256
public static function update(string $table, $id, array $data): bool
{
    if (empty($data)) {
        return false;
    }

    $col = [];
    $params = [];

    foreach ($data as $column => $value) {
        $col[] = "$column = ?";
        $params[] = $value;
    }

    $params[] = $id;
    $sql = "UPDATE {$table} SET " . implode(', ', $col) . " WHERE id = ?";

    $result = Database::send($sql, $params);
    return is_array($result) || $result === [];
}
```

**Delete в Auth.php:142-155:**
```php
// Подписка удаляется при истечении (Client.php:38)
Database::send('DELETE FROM qwees_subscriptions WHERE uniID = ?', [$uniID]);

// Удаление клиента из X-UI
$xray->DeleteKey($uniID);
```

**Массовые операции:**
```php
// Массовый update (можно добавить):
public static function bulkUpdate(string $table, string $whereColumn, $whereValue, array $data): bool {
    $col = [];
    $params = [];
    foreach ($data as $column => $value) {
        $col[] = "$column = ?";
        $params[] = $value;
    }
    $params[] = $whereValue;
    $sql = "UPDATE {$table} SET " . implode(', ', $col) . " WHERE $whereColumn = ?";
    return Database::send($sql, $params);
}

// Массовый delete:
public static function bulkDelete(string $table, string $column, $value): bool {
    return Database::send("DELETE FROM $table WHERE $column = ?", [$value]);
}
```

**Реализовано:**
- ✅ `update()` — `AdminDatabase::update()`
- ✅ `delete()` — `Database::send("DELETE FROM...")`
- ⚠️ Массовые операции — реализованы, но нет высокоуровневого API как в Django

---

### ❌ 6. Использование кеш-фреймворка

**Статус: НЕТ**

**Django-вариант:**
```python
from django.core.cache import cache

# Установка
 cache.set('user_1', user_data, 300)  # 5 минут

# Получение
user_data = cache.get('user_1')

# Кеширование view
@cache_page(60 * 15)  # 15 минут
def my_view(request):
    ...
```

**QweesCore:**
- ❌ Нет модуля кеширования
- ❌ Нет Redis/Memcached интеграции
- ❌ Нет декораторов для кеширования views
- ✅ Есть только сессии (`$_SESSION`)

**Для реализации можно добавить:**
```php
// composer require symfony/cache
use Symfony\Contracts\Cache\CacheInterface;

class Cache {
    public static function get(string $key) { ... }
    public static function set(string $key, $value, int $ttl = 3600) { ... }
    public static function delete(string $key) { ... }
}
```

---

### ❌ 7. F expressions

**Статус: НЕТ**

**Django-вариант:**
```python
from django.db.models import F

# Обновление на основе текущего значения поля
Article.objects.update(views=F('views') + 1)  # views = views + 1

# Сравнение полей
Article.objects.filter(stock__lte=F('sold'))  # stock <= sold
```

**QweesCore:**
- ❌ Нет F-expressions
- ❌ Нельзя сделать `UPDATE ... SET views = views + 1` через ORM
- ✅ Можно сделать через прямой SQL:

```php
// Ручной SQL для инкремента:
Database::send("UPDATE articles SET views = views + 1 WHERE id = ?", [$id]);
```

**Для добавления в архитектуру:**
```php
class F {
    private $field;
    public function __construct($field) { $this->field = $field; }
    public function plus($value) { return "{$this->field} + $value"; }
    public function minus($value) { return "{$this->field} - $value"; }
}

// Использование:
Database::updateF('articles', $id, ['views' => (new F('views'))->plus(1)]);
```

---

### ✅ 8. The Http404 exception

**Статус: ЕСТЬ (Полная реализация)**

**Django-вариант:**
```python
from django.shortcuts import get_object_or_404, render
from django.http import Http404

def article_detail(request, pk):
    # Вариант 1: автоматический 404
    article = get_object_or_404(Article, pk=pk)
    
    # Вариант 2: ручной 404
    try:
        article = Article.objects.get(pk=pk)
    except Article.DoesNotExist:
        raise Http404("Статья не найдена")
    
    return render(request, 'article.html', {'article': article})
```

**QweesCore-вариант:**
```php
// app/Models/Router/Routes.php:250-257
public static function error_404(string $path) {
    $link = dirname(__DIR__, 2) . '/Models/Router/view/404/404.html';
    if (file_exists($link)) {
        include_once $link;
    }
}

// public/pages/admin/edit.php:17-19 — редирект если запись не найдена
$row = AdminDatabase::getRow($table, $id);
if (!$row) {
    Network::onRedirect("/admin/database?table=" . urlencode($table));
}

// app/Controllers/API/API.php:114-123 — API 404
if (!$isAPI) {
    $api_result['status'] = 'qwees_crash';
    $api_result['message'] = 'Invalid API URL...';
    $httpCode = 404;
}
```

**Использование 404:**
```php
// В Routes.php при ненайденном маршруте:
header("HTTP/1.1 404 Страница не найдена");
self::error_404($route);

// Редирект при отсутствии объекта:
if (empty($table) || empty($id)) {
    Network::onRedirect('/admin');  // 302 redirect
}
```

**Реализовано:**
- ✅ 404 страница (`Routes::error_404()`)
- ✅ HTTP 404 статус (`http_response_code(404)`)
- ✅ Редирект при отсутствии объекта (`Network::onRedirect()`)

---

## Итоговая таблица

| Django-функция | В QweesCore | Реализация | Полная аналогия |
|----------------|-------------|------------|-----------------|
| models.URLField() | ⚠️ | `filter_var($url, FILTER_VALIDATE_URL)` | Частично |
| __icontains | ✅ | `LOWER(col) LIKE '%val%'` | Да |
| __contains | ⚠️ | Можно добавить без LOWER | Частично |
| values() | ⚠️ | Ручной `SELECT col1, col2` | Частично |
| values_list() | ⚠️ | `array_column()` | Частично |
| count() | ✅ | `AdminDatabase::getCount()` | Да |
| exists() | ✅ | `SELECT 1 ... LIMIT 1` | Да |
| update() | ✅ | `AdminDatabase::update()` | Да |
| delete() | ✅ | `Database::send("DELETE...")` | Да |
| Кеш-фреймворк | ❌ | Отсутствует | Нет |
| F expressions | ❌ | Отсутствует | Нет |
| Http404 | ✅ | `Routes::error_404()` | Да |

---

## Сводка по всем 4 частям Django

### Часть 1: CRUD + ManyToMany + select_related + prefetch_related
- CRUD: ✅
- ManyToMany: ⚠️ (JOIN вместо ORM)
- select_related: ⚠️ (JOIN вручную)
- prefetch_related: ⚠️ (LEFT JOIN)

### Часть 2: ImageField + PDF + Actions + File Uploads  
- ImageField: ⚠️ (только аватары)
- PDF: ❌
- Actions: ⚠️ (базовые)
- File Uploads: ⚠️ (частично)

### Часть 3: redirect + custom methods + FileField
- redirect: ✅
- custom methods: ✅
- FileField: ❌

### Часть 4: URLField + contains + values/count + cache + F + 404
- URLField: ⚠️
- contains: ✅
- values/count: ✅/⚠️
- cache: ❌
- F expressions: ❌
- Http404: ✅

---

*Отчет сгенерирован: 10 мая 2026*
*Версия проекта: QweesCore 2.1.0*
