# [YooKassa API SDK](../home.md)

# Class: \YooKassa\Request\PosLink\CreatePosLinkRequestBuilder
### Namespace: [\YooKassa\Request\PosLink](../namespaces/yookassa-request-poslink.md)
---
**Summary:**

Класс, представляющий модель CreatePosLinkRequestBuilder.

**Description:**

Класс билдера объектов запросов к API на создание кассовой ссылки.

---
### Constants
* No constants found

---
### Properties
| Visibility | Name | Flag | Summary |
| ----------:| ---- | ---- | ------- |
| protected | [$currentObject](../classes/YooKassa-Request-PosLink-CreatePosLinkRequestBuilder.md#property_currentObject) |  | Собираемый объект запроса. |

---
### Methods
| Visibility | Name | Flag | Summary |
| ----------:| ---- | ---- | ------- |
| public | [__construct()](../classes/YooKassa-Common-AbstractRequestBuilder.md#method___construct) |  | Конструктор, инициализирует пустой запрос, который в будущем начнём собирать. |
| public | [build()](../classes/YooKassa-Request-PosLink-CreatePosLinkRequestBuilder.md#method_build) |  | Осуществляет сборку объекта запроса к API. |
| public | [setOptions()](../classes/YooKassa-Common-AbstractRequestBuilder.md#method_setOptions) |  | Устанавливает свойства запроса из массива. |
| public | [setPosLinkData()](../classes/YooKassa-Request-PosLink-CreatePosLinkRequestBuilder.md#method_setPosLinkData) |  | Устанавливает данные кассовой ссылки. |
| public | [setRecipient()](../classes/YooKassa-Request-PosLink-CreatePosLinkRequestBuilder.md#method_setRecipient) |  | Устанавливает получателя платежа по кассовой ссылке. |
| protected | [initCurrentObject()](../classes/YooKassa-Request-PosLink-CreatePosLinkRequestBuilder.md#method_initCurrentObject) |  | Инициализирует объект запроса, который в дальнейшем будет собираться билдером. |

---
### Details
* File: [lib/Request/PosLink/CreatePosLinkRequestBuilder.php](../../lib/Request/PosLink/CreatePosLinkRequestBuilder.php)
* Package: YooKassa\Request
* Class Hierarchy: 
  * [\YooKassa\Common\AbstractRequestBuilder](../classes/YooKassa-Common-AbstractRequestBuilder.md)
  * \YooKassa\Request\PosLink\CreatePosLinkRequestBuilder

* See Also:
  * [](https://yookassa.ru/developers/api)

---
### Tags
| Tag | Version | Description |
| --- | ------- | ----------- |
| category |  | Class |
| author |  | cms@yoomoney.ru |

---
## Properties
<a name="property_currentObject"></a>
#### protected $currentObject : ?\YooKassa\Common\AbstractRequestInterface
---
**Summary**

Собираемый объект запроса.

**Type:** <a href="../?\YooKassa\Common\AbstractRequestInterface"><abbr title="?\YooKassa\Common\AbstractRequestInterface">AbstractRequestInterface</abbr></a>

**Details:**



---
## Methods
<a name="method___construct" class="anchor"></a>
#### public __construct() : mixed

```php
public __construct() : mixed
```

**Summary**

Конструктор, инициализирует пустой запрос, который в будущем начнём собирать.

**Details:**
* Inherited From: [\YooKassa\Common\AbstractRequestBuilder](../classes/YooKassa-Common-AbstractRequestBuilder.md)

**Returns:** mixed - 


<a name="method_build" class="anchor"></a>
#### public build() : \YooKassa\Request\PosLink\CreatePosLinkRequestInterface|\YooKassa\Common\AbstractRequestInterface

```php
public build(array|null $options = null) : \YooKassa\Request\PosLink\CreatePosLinkRequestInterface|\YooKassa\Common\AbstractRequestInterface
```

**Summary**

Осуществляет сборку объекта запроса к API.

**Details:**
* Inherited From: [\YooKassa\Request\PosLink\CreatePosLinkRequestBuilder](../classes/YooKassa-Request-PosLink-CreatePosLinkRequestBuilder.md)

##### Parameters:
| Type | Name | Description |
| ---- | ---- | ----------- |
| <code lang="php">array OR null</code> | options  |  |

**Returns:** \YooKassa\Request\PosLink\CreatePosLinkRequestInterface|\YooKassa\Common\AbstractRequestInterface - 


<a name="method_setOptions" class="anchor"></a>
#### public setOptions() : \YooKassa\Common\AbstractRequestBuilder

```php
public setOptions(iterable|null $options) : \YooKassa\Common\AbstractRequestBuilder
```

**Summary**

Устанавливает свойства запроса из массива.

**Details:**
* Inherited From: [\YooKassa\Common\AbstractRequestBuilder](../classes/YooKassa-Common-AbstractRequestBuilder.md)

##### Parameters:
| Type | Name | Description |
| ---- | ---- | ----------- |
| <code lang="php">iterable OR null</code> | options  | Массив свойств запроса |

##### Throws:
| Type | Description |
| ---- | ----------- |
| \InvalidArgumentException | Выбрасывается если аргумент не массив и не итерируемый объект |
| \YooKassa\Common\Exceptions\InvalidPropertyException | Выбрасывается если не удалось установить один из параметров, переданных в массиве настроек |

**Returns:** \YooKassa\Common\AbstractRequestBuilder - Инстанс текущего билдера запросов


<a name="method_setPosLinkData" class="anchor"></a>
#### public setPosLinkData() : \YooKassa\Request\PosLink\CreatePosLinkRequestBuilder

```php
public setPosLinkData(mixed $value) : \YooKassa\Request\PosLink\CreatePosLinkRequestBuilder
```

**Summary**

Устанавливает данные кассовой ссылки.

**Details:**
* Inherited From: [\YooKassa\Request\PosLink\CreatePosLinkRequestBuilder](../classes/YooKassa-Request-PosLink-CreatePosLinkRequestBuilder.md)

##### Parameters:
| Type | Name | Description |
| ---- | ---- | ----------- |
| <code lang="php">mixed</code> | value  | Данные кассовой ссылки |

**Returns:** \YooKassa\Request\PosLink\CreatePosLinkRequestBuilder - Инстанс текущего билдера


<a name="method_setRecipient" class="anchor"></a>
#### public setRecipient() : \YooKassa\Request\PosLink\CreatePosLinkRequestBuilder

```php
public setRecipient(mixed $value) : \YooKassa\Request\PosLink\CreatePosLinkRequestBuilder
```

**Summary**

Устанавливает получателя платежа по кассовой ссылке.

**Details:**
* Inherited From: [\YooKassa\Request\PosLink\CreatePosLinkRequestBuilder](../classes/YooKassa-Request-PosLink-CreatePosLinkRequestBuilder.md)

##### Parameters:
| Type | Name | Description |
| ---- | ---- | ----------- |
| <code lang="php">mixed</code> | value  | Получатель платежа по кассовой ссылке |

**Returns:** \YooKassa\Request\PosLink\CreatePosLinkRequestBuilder - Инстанс текущего билдера


<a name="method_initCurrentObject" class="anchor"></a>
#### protected initCurrentObject() : \YooKassa\Request\PosLink\CreatePosLinkRequest

```php
protected initCurrentObject() : \YooKassa\Request\PosLink\CreatePosLinkRequest
```

**Summary**

Инициализирует объект запроса, который в дальнейшем будет собираться билдером.

**Details:**
* Inherited From: [\YooKassa\Request\PosLink\CreatePosLinkRequestBuilder](../classes/YooKassa-Request-PosLink-CreatePosLinkRequestBuilder.md)

**Returns:** \YooKassa\Request\PosLink\CreatePosLinkRequest - Инстанс собираемого объекта запроса к API



---

### Top Namespaces

* [\YooKassa](../namespaces/yookassa.md)

---

### Reports
* [Errors - 0](../reports/errors.md)
* [Markers - 0](../reports/markers.md)
* [Deprecated - 43](../reports/deprecated.md)

---

This document was automatically generated from source code comments on 2026-08-13 using [phpDocumentor](http://www.phpdoc.org/)

&copy; 2026 YooMoney