# Сравнительный анализ: QweesCore Admin vs Django 4

## Задание из учебника Django 4. Часть 2

### Требуемый функционал Django:
1. Демонстрация создания, редактирования, удаления на сайте
2. Файл requirements.txt
3. models.ManyToManyField с параметром `through`
4. select_related()
5. prefetch_related()

---

## Отчет о наличии/отсутствии функционала

### ✅ 1. CRUD операции (Создание, Редактирование, Удаление)

**Статус: ЕСТЬ (Полная реализация)**

| Операция | Реализация | Файл |
|----------|------------|------|
| **CREATE** | `INSERT INTO` через `Database::send()` | `app/Config/Database.php:313-388` |
| **READ** | `SELECT` с JOIN через `Database::send()` | `app/Config/Database.php:356-359` |
| **UPDATE** | `UPDATE` с проверкой rowCount | `app/Config/Database.php:363-375` |
| **DELETE** | `DELETE FROM` через PDO | `app/Config/Database.php:363-375` |

**Примеры CRUD в админ-панели:**
```php
// Создание пользователя (Auth.php:148-155)
Database::send("INSERT INTO qwees_subscriptions (uniID, status, ...) VALUES (?, ?, ...)", [...]);

// Редактирование (database.php + edit.php)
// Удаление - через DELETE запросы
```

**Админ-панель реализует:**
- ✅ Добавление пользователей (`/admin/addUser`)
- ✅ Редактирование записей (`/admin/edit?table=&id=`)
- ✅ Удаление подписок (автоматически при истечении)
- ✅ Просмотр всех таблиц (`/admin/database?table=`)

---

### ❌ 2. Файл requirements.txt

**Статус: НЕТ (Есть аналог)**

Django-специфичный файл `requirements.txt` **отсутствует**.

**Аналог в проекте:**
- `composer.json` - зависимости PHP (Composer)
- `package.json` - скрипты запуска

```json
// composer.json
"dependencies": {
  "php": "^8.0.0"
}
```

**Вывод:** Требуется Python-файл `requirements.txt` для Django, в PHP-проекте используется `composer.json`.

---

### ⚠️ 3. ManyToManyField с through

**Статус: ЧАСТИЧНО (Нет нативной поддержки)**

**Django-вариант:**
```python
class Membership(models.Model):
    person = models.ForeignKey(Person, on_delete=models.CASCADE)
    group = models.ForeignKey(Group, on_delete=models.CASCADE)
    date_joined = models.DateField()
    invite_reason = models.CharField(max_length=64)

class Person(models.Model):
    groups = models.ManyToManyField(Group, through='Membership')
```

**QweesCore-вариант (аналог):**
```php
// app/Models/Article/Article.php:167-169
"SELECT art.*, user.username 
FROM {$this->table_name} art 
JOIN " . (new User())->table_name . " user 
ON art.user_id = user.id"
```

**Реализовано через:**
- Ручной `JOIN` в SQL-запросах
- Связь `article.user_id` → `users.id`

**Отличие от Django:**
- ❌ Нет автоматического управления промежуточной таблицей
- ❌ Нет ORM-уровня для ManyToMany
- ✅ Есть SQL JOIN (ручная реализация)

---

### ⚠️ 4. select_related()

**Статус: ЧАСТИЧНО (SQL JOIN вручную)**

**Django-вариант:**
```python
# Оптимизация запроса - один JOIN вместо N+1
Article.objects.select_related('author').get(id=1)
```

**QweesCore-вариант:**
```php
// app/Models/Article/Article.php:253-259
"SELECT art.*, user.username 
FROM {$this->table_name} art 
JOIN " . (new User())->table_name . " user 
ON art.user_id = user.id 
WHERE user.id = ?"
```

**Места использования:**
- `Article.php` - JOIN с таблицей users
- `Client.php` - LEFT JOIN qwees_subscriptions

**Вывод:** Функциональность `select_related()` реализована через прямые SQL JOIN, но:
- ❌ Нет автоматической оптимизации
- ❌ Нет ORM-уровня
- ✅ Есть ручной контроль JOIN

---

### ⚠️ 5. prefetch_related()

**Статус: ЧАСТИЧНО (Отдельные запросы)**

**Django-вариант:**
```python
# Для ManyToMany и обратных связей
Article.objects.prefetch_related('comments', 'tags').all()
```

**QweesCore-вариант:**
```php
// Client.php:15-20 - один запрос с LEFT JOIN
'SELECT u.*, s.status as sub_status, s.subscription, ...
FROM qwees_users u 
LEFT JOIN qwees_subscriptions s ON u.uniID = s.uniID 
WHERE u.uniID = ?'
```

**Отличия:**
- Django: Два запроса (основной + prefetch) + Python-level join
- QweesCore: Один запрос с SQL LEFT JOIN

**Вывод:** Функционально аналогично, но реализовано через SQL JOIN вместо отдельных запросов.

---

## Итоговая таблица

| Django-функция | В QweesCore | Способ реализации | Полная аналогия |
|----------------|-------------|---------------------|-----------------|
| CRUD | ✅ Есть | `Database::send()` с SQL | Да |
| requirements.txt | ❌ Нет | `composer.json` | Нет (Python vs PHP) |
| ManyToMany+through | ⚠️ Частично | Ручной SQL JOIN | Нет |
| select_related() | ⚠️ Частично | Ручной SQL JOIN | Нет |
| prefetch_related() | ⚠️ Частично | LEFT JOIN в SQL | Частично |

---

## Рекомендации

### Для полного соответствия Django:

1. **ManyToMany through** - Создать промежуточные таблицы явно:
   ```sql
   CREATE TABLE article_tags (
       article_id INT,
       tag_id INT,
       added_by VARCHAR(64),
       created_at TIMESTAMP,
       FOREIGN KEY (article_id) REFERENCES articles(id),
       FOREIGN KEY (tag_id) REFERENCES tags(id)
   );
   ```

2. **ORM-уровень** - Добавить класс QueryBuilder с поддержкой:
   - `$query->with('subscriptions')` - аналог `select_related()`
   - `$query->load('comments')` - аналог `prefetch_related()`

3. **Миграции** - Добавить систему миграций как в Django

---

## Сравнение архитектур

| Аспект | Django (Python) | QweesCore (PHP) |
|--------|-----------------|-----------------|
| ORM | Django ORM (полноценный) | Отсутствует (сырой SQL) |
| Миграции | `makemigrations` | `schema.sql` + ручной SQL |
| Админ-панель | Автогенерируемая | Кастомная (реализована) |
| ManyToMany | Нативная поддержка | Ручные JOIN |
| Зависимости | `requirements.txt` | `composer.json` |

---

*Отчет сгенерирован: 10 мая 2026*
*Версия проекта: QweesCore 2.1.0*
