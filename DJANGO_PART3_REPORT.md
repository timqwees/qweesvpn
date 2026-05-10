# Сравнительный анализ: QweesCore vs Django 4. Часть 3

## Задание из учебника Django 4. Часть 3

### Требуемый функционал Django:
1. `models.ImageField` — поле для изображений
2. `return redirect` — редирект после операций
3. Генерация PDF документа в админке (стр. 488)
4. Добавить действие на сайт администрирования
5. `models.FileField` — поле для файлов
6. Создание собственного функционального метода в модели (стр. 410)
7. File Uploads — особенности сохранения файлов

---

## Отчет о наличии/отсутствии функционала

### ⚠️ 1. models.ImageField

**Статус: ЧАСТИЧНО (Ручная реализация)**

**Django-вариант:**
```python
class User(models.Model):
    avatar = models.ImageField(upload_to='avatars/', blank=True)
```

**QweesCore-вариант:**
```php
// app/Models/User/User.php:209-261
function uploadFile(array $file, string $prefix = '', ?string $customName = null): string|false
{
    // Проверка типа (только изображения)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new \Exception('Недопустимый тип файла...');
    }
    
    // Сохранение в public/avatar/
    $uploadPath = __DIR__ . '/../../../public/avatar';
    // ...
    return "avatar/$fileName";  // путь сохраняется в БД
}
```

**Отличия:**
- ❌ Нет ORM-уровня ImageField
- ❌ Нет автоматического resize/validate
- ✅ Есть ручная загрузка с проверкой MIME-type
- ✅ Есть ограничение размера (5MB)

---

### ✅ 2. return redirect

**Статус: ЕСТЬ (Полная реализация)**

**Django-вариант:**
```python
from django.shortcuts import redirect

def delete_view(request, pk):
    obj = get_object_or_404(MyModel, pk=pk)
    obj.delete()
    return redirect('admin:index')  # после удаления
```

**QweesCore-вариант:**
```php
// app/Models/Network/Network.php:266-318
public static function onRedirect(string $path)
{
    // Проверка на циклические редиректы
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $currentUri === $normalizedPath) {
        throw new \Exception("Обнаружен циклический редирект на: " . $path);
    }
    
    header("Location: " . $path, true, 302);
    exit();
}
```

**Использование после операций:**
```php
// public/pages/admin/edit.php:12,18
if (empty($table) || empty($id)) {
    Network::onRedirect('/admin');  // редирект если нет параметров
}

if (!$row) {
    Network::onRedirect("/admin/database?table=" . urlencode($table));  // если запись не найдена
}

// setting/route/function/Controllers/admin/AdminDatabase.php
Network::onRedirect('/admin?message_status=error&message_msg=Ошибка добавления!');
```

**Реализовано:**
- ✅ После удаления объекта
- ✅ После добавления объекта
- ✅ При обращении к несуществующему объекту
- ✅ Защита от циклических редиректов

---

### ✅ 3. Генерация PDF документа в админке (стр. 488)

**Статус: ЕСТЬ (Реализовано)**

**Django-вариант:**
```python
from django.http import HttpResponse
from reportlab.pdfgen import canvas

def export_pdf(request):
    response = HttpResponse(content_type='application/pdf')
    response['Content-Disposition'] = 'attachment; filename="report.pdf"'
    
    p = canvas.Canvas(response)
    p.drawString(100, 700, "Hello PDF")
    p.showPage()
    p.save()
    return response
```

**QweesCore-вариант:**
```php
// setting/route/function/Controllers/admin/PdfController.php
use Mpdf\Mpdf;

class PdfController {
    public function exportTableToPdf(string $table): void {
        $data = AdminDatabase::getData($table, 1000);
        $mpdf = new Mpdf(['default_font' => 'dejavusans']);
        $mpdf->WriteHTML($html);
        $mpdf->Output($table . '_report.pdf', 'D');
    }
}

// Маршрут: /export/pdf?type=table&table=qwees_users
```

**Реализовано:**
- ✅ Библиотека mPDF (`composer require mpdf/mpdf`)
- ✅ `PdfController` с методами:
  - `exportTableToPdf()` — экспорт любой таблицы
  - `exportSubscriptionStats()` — статистика подписок
  - `exportUserStats()` — статистика пользователей
- ✅ Кнопки в админке (database.php и index.php)
- ✅ Поддержка русского языка (DejaVu Sans)
- ✅ Красивое форматирование с Tailwind CSS стилями

**Кнопки в интерфейсе:**
- `/admin/database` — экспорт текущей таблицы, подписок, пользователей
- `/admin/index` — быстрый экспорт статистики

---

### ⚠️ 4. Добавить действие на сайт администрирования

**Статус: ЧАСТИЧНО (Нет массовых действий)**

**Django-вариант:**
```python
@admin.register(MyModel)
class MyModelAdmin(admin.ModelAdmin):
    actions = ['make_published', 'export_csv']
    
    @admin.action(description='Mark selected as published')
    def make_published(self, request, queryset):
        queryset.update(status='published')
```

**QweesCore-вариант (действия в форме редактирования):**
```php
// public/pages/admin/edit.php:131-140
<div class="flex gap-3">
    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg">
        Сохранить
    </button>
    <a href="/admin/database?table=<?= htmlspecialchars($table) ?>" class="bg-gray-100...">
        Отмена
    </a>
</div>
```

**Реализовано:**
- ✅ Просмотр записи (клик по ID в database.php)
- ✅ Редактирование записи (/admin/edit)
- ✅ Сохранение изменений (/admin/save)

**Не реализовано:**
- ❌ Массовые действия (select all → delete/publish)
- ❌ Кастомные действия ("Сделать активным", "Экспорт")
- ❌ Dropdown с действиями над списком

---

### ⚠️ 5. models.FileField

**Статус: ЧАСТИЧНО (Только ImageField)**

**Django-вариант:**
```python
class Document(models.Model):
    file = models.FileField(upload_to='documents/%Y/%m/%d/')
    image = models.ImageField(upload_to='images/')
```

**QweesCore-вариант:**
```php
// Только для изображений (User.php:209-261)
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
```

**Отличия:**
- ❌ Нет универсального FileField (PDF, DOC, XLS)
- ❌ Нет структуры папок по дате (%Y/%m/%d)
- ❌ Нет валидации расширений для документов
- ✅ Есть для изображений (avatar/)

---

### ✅ 6. Собственный функциональный метод в модели (стр. 410)

**Статус: ЕСТЬ**

**Django-вариант:**
```python
class Article(models.Model):
    title = models.CharField(max_length=200)
    
    def get_absolute_url(self):
        return reverse('article_detail', kwargs={'pk': self.pk})
    
    def word_count(self):
        return len(self.content.split())
```

**QweesCore-вариант:**
```php
// app/Models/User/User.php:147-178
public function onUpdateProfile(string $tableName, array $new_data, int $userId)
{
    // Кастомный метод обновления профиля
    foreach ($new_data as $column => $value) {
        Network::onColumnExists($column, $tableName);  // проверка колонки
    }
    // ... UPDATE логика
}

// app/Models/Article/Article.php:79-101
public function addArticle(string $title, string $content, int $userId)
{
    $timestamp = ($driver === 'sqlite') ? 'CURRENT_TIMESTAMP' : 'NOW()';
    $result = Database::send(
        "INSERT INTO {$this->table_name} (title, content, user_id, created_at) VALUES (?, ?, ?, $timestamp)",
        [$title, $content, $userId]
    );
    return $result;
}

// app/Models/Article/Article.php:121-144
public function removeArticle(int $id, int $userId)
{
    Database::getConnection()->beginTransaction();  // транзакция
    $result = Database::send(
        "DELETE FROM {$this->table_name} WHERE id = ? AND user_id = ?",
        [$id, $userId]
    );
    if ($result) {
        Database::getConnection()->commit();
    } else {
        Database::getConnection()->rollBack();
    }
    return $result;
}
```

**Реализовано:**
- ✅ Кастомные методы моделей (addArticle, removeArticle, onUpdateProfile)
- ✅ Транзакции в методах (beginTransaction, commit, rollBack)
- ✅ Валидация внутри методов
- ✅ Уведомления через Message::set()

---

### ⚠️ 7. File Uploads — особенности сохранения файлов

**Статус: ЧАСТИЧНО**

**Архитектура в QweesCore:**

```php
// app/Models/User/User.php:209-261
function uploadFile(array $file, string $prefix = '', ?string $customName = null): string|false
{
    // 1. Валидация MIME-type (не расширения!)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    // 2. Валидация размера (5MB)
    if ($file['size'] > 5 * 1024 * 1024) { ... }
    
    // 3. Путь: public/avatar/ (web-доступная директория)
    $uploadPath = __DIR__ . '/../../../public/avatar';
    
    // 4. Создание директории если нет
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }
    
    // 5. Очистка имени файла
    $customName = preg_replace('/[^a-zA-Z0-9_-]/', '', $customName);
    
    // 6. Проверка дубликатов (добавляем timestamp)
    if (file_exists($fullPath)) {
        $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '_' . time() . ".$ext";
    }
    
    // 7. Безопасное перемещение
    move_uploaded_file($file['tmp_name'], $fullPath);
    
    // 8. Возврат пути для БД
    return "avatar/$fileName";
}
```

**Особенности сохранения в формах:**

```html
<!-- В форме должно быть: -->
<form action="/admin/save" method="POST" enctype="multipart/form-data">
    <input type="file" name="avatar" accept="image/*">
</form>
```

**Текущие ограничения:**
- ❌ Нет `enctype="multipart/form-data"` в формах админки (edit.php)
- ❌ Нет обработки $_FILES в AdminDatabase
- ❌ Только изображения, не универсальные файлы
- ✅ Путь сохраняется как строка в БД (VARCHAR/TEXT)
- ✅ Физическое хранение в public/ (web-доступно)

**Сравнение с Django:**

| Аспект | Django | QweesCore |
|--------|--------|-----------|
| Валидация | Формы/Модели | Ручная в методе |
| Хранение пути | FileField (CharField) | Строка в БД |
| Загрузка | Форма с enctype | Не реализована в админке |
| Сохранение | instance.save() | Ручный INSERT/UPDATE |
| Путь файла | upload_to='path/' | Хардкод 'avatar/' |

---

## Итоговая таблица

| Django-функция | В QweesCore | Реализация | Полная аналогия |
|----------------|-------------|------------|-----------------|
| models.ImageField | ⚠️ | `uploadFile()` только для аватаров | Нет |
| return redirect | ✅ | `Network::onRedirect()` | Да |
| Генерация PDF | ✅ | `PdfController` с mPDF | Да |
| Действия в админке | ⚠️ | Базовые + PDF экспорт | Частично |
| models.FileField | ❌ | Нет универсального | Нет |
| Кастомные методы модели | ✅ | `addArticle()`, `removeArticle()` | Да |
| File Uploads | ⚠️ | Только изображения, нет в админке | Частично |

---

## Реализовано: PDF генерация ✓

PDF экспорт полностью реализован через `PdfController` с использованием библиотеки **mPDF**.

### Доступные функции:
- **Экспорт таблицы** — `/export/pdf?type=table&table=TABLE_NAME`
- **Статистика подписок** — `/export/pdf?type=subscriptions`
- **Статистика пользователей** — `/export/pdf?type=users`

### Для добавления новых отчетов:
```php
// В PdfController добавить метод:
public function exportCustomReport(): void {
    $data = // получить данные
    $html = $this->generateCustomHtml($data);
    $this->mpdf->WriteHTML($html);
    $this->mpdf->Output('custom_report.pdf', 'D');
}
```

---

## Рекомендации для реализации оставшегося функционала

### 1. Добавить массовые действия
```php
// В database.php добавить:
<form action="/admin/bulk-action" method="POST">
    <select name="action">
        <option value="delete">Удалить выбранные</option>
        <option value="export">Экспорт CSV</option>
    </select>
    <input type="checkbox" name="selected[]" value="<?= $row['id'] ?>">
</form>
```

### 3. Улучшить File Upload
```php
// Добавить в edit.php:
if ($_FILES) {
    $filePath = $admin->uploadFile($_FILES['file'], $id);
    $data['file_path'] = $filePath;
}
```

---

## Сводка по реализации Django 4 Часть 3

### Реализовано ✅
1. **return redirect** — `Network::onRedirect()` после всех операций
2. **Генерация PDF** — Полная реализация через mPDF
3. **Кастомные методы модели** — `addArticle()`, `removeArticle()`, `uploadFile()`

### Частично реализовано ⚠️
1. **ImageField** — Только для аватаров, не универсальный FileField
2. **Действия в админке** — Базовые CRUD + PDF экспорт (нет массовых действий)
3. **File Uploads** — Нет интеграции в формы админки

### Ключевые файлы для демонстрации:
- **PDF Controller:** `setting/route/function/Controllers/admin/PdfController.php`
- **Маршруты:** `setting/route/routes.php:78` — `/export/pdf`
- **UI кнопки:** `public/pages/admin/database.php:99-124`
- **UI в dashboard:** `public/pages/admin/index.php:141-159`

---

*Отчет сгенерирован: 10 мая 2026*  
*Версия проекта: QweesCore 2.1.0*  
*PDF модуль: mPDF v8.x*
