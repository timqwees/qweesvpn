# [YooKassa API SDK](../home.md)

# Class: \YooKassa\Request\PosLink\RecipientPosLinkRequestBuilder
### Namespace: [\YooKassa\Request\PosLink](../namespaces/yookassa-request-poslink.md)
---
**Summary:**

Класс, представляющий модель RecipientPosLinkRequestBuilder.

**Description:**

Класс билдера объектов запросов на привязку получателя к кассовой ссылке.

---
### Constants
* No constants found

---
### Properties
| Visibility | Name | Flag | Summary |
| ----------:| ---- | ---- | ------- |
| protected | [$currentObject](../classes/YooKassa-Request-PosLink-RecipientPosLinkRequestBuilder.md#property_currentObject) |  | Собираемый объект запроса. |

---
### Methods
| Visibility | Name | Flag | Summary |
| ----------:| ---- | ---- | ------- |
| public | [__construct()](../classes/YooKassa-Common-AbstractRequestBuilder.md#method___construct) |  | Конструктор, инициализирует пустой запрос, который в будущем начнём собирать. |
| public | [build()](../classes/YooKassa-Request-PosLink-RecipientPosLinkRequestBuilder.md#method_build) |  | Осуществляет сборку объекта запроса к API. |
| public | [setOptions()](../classes/YooKassa-Common-AbstractRequestBuilder.md#method_setOptions) |  | Устанавливает свойства запроса из массива. |
| public | [setRecipient()](../classes/YooKassa-Request-PosLink-RecipientPosLinkRequestBuilder.md#method_setRecipient) |  | Устанавливает получателя платежа по кассовой ссылке. |
| protected | [initCurrentObject()](../classes/YooKassa-Request-PosLink-RecipientPosLinkRequestBuilder.md#method_initCurrentObject) |  | Инициализирует объект запроса, который в дальнейшем будет собираться билдером. |

---
### Details
* File: [lib/Request/PosLink/RecipientPosLinkRequestBuilder.php](../../lib/Request/PosLink/RecipientPosLinkRequestBuilder.php)
* Package: YooKassa\Request
* Class Hierarchy: 
  * [\YooKassa\Common\AbstractRequestBuilder](../classes/YooKassa-Common-AbstractRequestBuilder.md)
  * \YooKassa\Request\PosLink\RecipientPosLinkRequestBuilder

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
#### public build() : \YooKassa\Request\PosLink\RecipientPosLinkRequestInterface|\YooKassa\Common\AbstractRequestInterface

```php
public build(array|null $options = null) : \YooKassa\Request\PosLink\RecipientPosLinkRequestInterface|\YooKassa\Common\AbstractRequestInterface
```

**Summary**

Осуществляет сборку объекта запроса к API.

**Details:**
* Inherited From: [\YooKassa\Request\PosLink\RecipientPosLinkRequestBuilder](../classes/YooKassa-Request-PosLink-RecipientPosLinkRequestBuilder.md)

##### Parameters:
| Type | Name | Description |
| ---- | ---- | ----------- |
| <code lang="php">array OR null</code> | options  |  |

**Returns:** \YooKassa\Request\PosLink\RecipientPosLinkRequestInterface|\YooKassa\Common\AbstractRequestInterface - 


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


<a name="method_setRecipient" class="anchor"></a>
#### public setRecipient() : \YooKassa\Request\PosLink\RecipientPosLinkRequestBuilder

```php
public setRecipient(mixed $value) : \YooKassa\Request\PosLink\RecipientPosLinkRequestBuilder
```

**Summary**

Устанавливает получателя платежа по кассовой ссылке.

**Details:**
* Inherited From: [\YooKassa\Request\PosLink\RecipientPosLinkRequestBuilder](../classes/YooKassa-Request-PosLink-RecipientPosLinkRequestBuilder.md)

##### Parameters:
| Type | Name | Description |
| ---- | ---- | ----------- |
| <code lang="php">mixed</code> | value  | Получатель платежа по кассовой ссылке |

**Returns:** \YooKassa\Request\PosLink\RecipientPosLinkRequestBuilder - Инстанс текущего билдера


<a name="method_initCurrentObject" class="anchor"></a>
#### protected initCurrentObject() : \YooKassa\Request\PosLink\RecipientPosLinkRequest

```php
protected initCurrentObject() : \YooKassa\Request\PosLink\RecipientPosLinkRequest
```

**Summary**

Инициализирует объект запроса, который в дальнейшем будет собираться билдером.

**Details:**
* Inherited From: [\YooKassa\Request\PosLink\RecipientPosLinkRequestBuilder](../classes/YooKassa-Request-PosLink-RecipientPosLinkRequestBuilder.md)

**Returns:** \YooKassa\Request\PosLink\RecipientPosLinkRequest - Инстанс собираемого объекта запроса к API



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