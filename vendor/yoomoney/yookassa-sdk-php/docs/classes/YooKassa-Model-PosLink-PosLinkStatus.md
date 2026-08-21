# [YooKassa API SDK](../home.md)

# Class: \YooKassa\Model\PosLink\PosLinkStatus
### Namespace: [\YooKassa\Model\PosLink](../namespaces/yookassa-model-poslink.md)
---
**Summary:**

Класс, представляющий модель PosLinkStatus.

**Description:**

Статус кассовой ссылки.

Возможные значения:
 * ~`active` — кассовая ссылка активна и готова к приему платежей;
 * ~`inactive` — кассовая ссылка неактивна, прием платежей недоступен.

---
### Constants
| Visibility | Name | Flag | Summary |
| ----------:| ---- | ---- | ------- |
| public | [ACTIVE](../classes/YooKassa-Model-PosLink-PosLinkStatus.md#constant_ACTIVE) |  | Кассовая ссылка активна и готова к приему платежей |
| public | [INACTIVE](../classes/YooKassa-Model-PosLink-PosLinkStatus.md#constant_INACTIVE) |  | Кассовая ссылка неактивна, прием платежей недоступен |

---
### Properties
| Visibility | Name | Flag | Summary |
| ----------:| ---- | ---- | ------- |
| protected | [$validValues](../classes/YooKassa-Model-PosLink-PosLinkStatus.md#property_validValues) |  | Возвращает список доступных значений. |

---
### Methods
| Visibility | Name | Flag | Summary |
| ----------:| ---- | ---- | ------- |
| public | [getEnabledValues()](../classes/YooKassa-Common-AbstractEnum.md#method_getEnabledValues) |  | Возвращает значения в enum'е значения которых разрешены. |
| public | [getValidValues()](../classes/YooKassa-Common-AbstractEnum.md#method_getValidValues) |  | Возвращает все значения в enum'e. |
| public | [valueExists()](../classes/YooKassa-Common-AbstractEnum.md#method_valueExists) |  | Проверяет наличие значения в enum'e. |

---
### Details
* File: [lib/Model/PosLink/PosLinkStatus.php](../../lib/Model/PosLink/PosLinkStatus.php)
* Package: YooKassa\Model
* Class Hierarchy: 
  * [\YooKassa\Common\AbstractEnum](../classes/YooKassa-Common-AbstractEnum.md)
  * \YooKassa\Model\PosLink\PosLinkStatus

* See Also:
  * [](https://yookassa.ru/developers/api)

---
### Tags
| Tag | Version | Description |
| --- | ------- | ----------- |
| category |  | Class |
| author |  | cms@yoomoney.ru |

---
## Constants
<a name="constant_ACTIVE" class="anchor"></a>
###### ACTIVE
Кассовая ссылка активна и готова к приему платежей

```php
ACTIVE = 'active'
```


<a name="constant_INACTIVE" class="anchor"></a>
###### INACTIVE
Кассовая ссылка неактивна, прием платежей недоступен

```php
INACTIVE = 'inactive'
```



---
## Properties
<a name="property_validValues"></a>
#### protected $validValues : array
---
**Summary**

Возвращает список доступных значений.

**Type:** <a href="../array"><abbr title="array">array</abbr></a>
Массив принимаемых enum&#039;ом значений
**Details:**


##### Tags
| Tag | Version | Description |
| --- | ------- | ----------- |
| return |  |  |


---
## Methods
<a name="method_getEnabledValues" class="anchor"></a>
#### public getEnabledValues() : string[]

```php
Static public getEnabledValues() : string[]
```

**Summary**

Возвращает значения в enum'е значения которых разрешены.

**Details:**
* Inherited From: [\YooKassa\Common\AbstractEnum](../classes/YooKassa-Common-AbstractEnum.md)

**Returns:** string[] - Массив разрешённых значений


<a name="method_getValidValues" class="anchor"></a>
#### public getValidValues() : array

```php
Static public getValidValues() : array
```

**Summary**

Возвращает все значения в enum'e.

**Details:**
* Inherited From: [\YooKassa\Common\AbstractEnum](../classes/YooKassa-Common-AbstractEnum.md)

**Returns:** array - Массив значений в перечислении


<a name="method_valueExists" class="anchor"></a>
#### public valueExists() : bool

```php
Static public valueExists(mixed $value) : bool
```

**Summary**

Проверяет наличие значения в enum'e.

**Details:**
* Inherited From: [\YooKassa\Common\AbstractEnum](../classes/YooKassa-Common-AbstractEnum.md)

##### Parameters:
| Type | Name | Description |
| ---- | ---- | ----------- |
| <code lang="php">mixed</code> | value  | Проверяемое значение |

**Returns:** bool - True если значение имеется, false если нет



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