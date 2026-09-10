# [YooKassa API SDK](../home.md)

# Interface: CreatePosLinkRequestInterface
### Namespace: [\YooKassa\Request\PosLink](../namespaces/yookassa-request-poslink.md)
---
**Summary:**

Interface CreatePosLinkRequestInterface.

---
### Constants
* No constants found

---
### Methods
| Visibility | Name | Flag | Summary |
| ----------:| ---- | ---- | ------- |
| public | [getPosLinkData()](../classes/YooKassa-Request-PosLink-CreatePosLinkRequestInterface.md#method_getPosLinkData) |  | Возвращает данные кассовой ссылки. |
| public | [getRecipient()](../classes/YooKassa-Request-PosLink-CreatePosLinkRequestInterface.md#method_getRecipient) |  | Возвращает получателя платежа по кассовой ссылке. |
| public | [setPosLinkData()](../classes/YooKassa-Request-PosLink-CreatePosLinkRequestInterface.md#method_setPosLinkData) |  | Устанавливает данные кассовой ссылки. |
| public | [setRecipient()](../classes/YooKassa-Request-PosLink-CreatePosLinkRequestInterface.md#method_setRecipient) |  | Устанавливает получателя платежа по кассовой ссылке. |

---
### Details
* File: [lib/Request/PosLink/CreatePosLinkRequestInterface.php](../../lib/Request/PosLink/CreatePosLinkRequestInterface.php)
* Package: \YooKassa\Request
* See Also:
  * [](https://yookassa.ru/developers/api)

---
### Tags
| Tag | Version | Description |
| --- | ------- | ----------- |
| category |  | Interface |
| author |  | cms@yoomoney.ru |
| property |  | Получатель платежа по кассовой ссылке |
| property |  | Данные кассовой ссылки |

---
## Methods
<a name="method_getRecipient" class="anchor"></a>
#### public getRecipient() : \YooKassa\Model\PosLink\PosLinkRecipient|null

```php
public getRecipient() : \YooKassa\Model\PosLink\PosLinkRecipient|null
```

**Summary**

Возвращает получателя платежа по кассовой ссылке.

**Details:**
* Inherited From: [\YooKassa\Request\PosLink\CreatePosLinkRequestInterface](../classes/YooKassa-Request-PosLink-CreatePosLinkRequestInterface.md)

**Returns:** \YooKassa\Model\PosLink\PosLinkRecipient|null - Получатель платежа по кассовой ссылке


<a name="method_setRecipient" class="anchor"></a>
#### public setRecipient() : self

```php
public setRecipient(\YooKassa\Model\PosLink\PosLinkRecipient|array|null $recipient = null) : self
```

**Summary**

Устанавливает получателя платежа по кассовой ссылке.

**Details:**
* Inherited From: [\YooKassa\Request\PosLink\CreatePosLinkRequestInterface](../classes/YooKassa-Request-PosLink-CreatePosLinkRequestInterface.md)

##### Parameters:
| Type | Name | Description |
| ---- | ---- | ----------- |
| <code lang="php">\YooKassa\Model\PosLink\PosLinkRecipient OR array OR null</code> | recipient  | Получатель платежа по кассовой ссылке |

**Returns:** self - 


<a name="method_getPosLinkData" class="anchor"></a>
#### public getPosLinkData() : \YooKassa\Request\PosLink\PosLinkData|null

```php
public getPosLinkData() : \YooKassa\Request\PosLink\PosLinkData|null
```

**Summary**

Возвращает данные кассовой ссылки.

**Details:**
* Inherited From: [\YooKassa\Request\PosLink\CreatePosLinkRequestInterface](../classes/YooKassa-Request-PosLink-CreatePosLinkRequestInterface.md)

**Returns:** \YooKassa\Request\PosLink\PosLinkData|null - Данные кассовой ссылки


<a name="method_setPosLinkData" class="anchor"></a>
#### public setPosLinkData() : self

```php
public setPosLinkData(\YooKassa\Request\PosLink\PosLinkData|array|null $pos_link_data = null) : self
```

**Summary**

Устанавливает данные кассовой ссылки.

**Details:**
* Inherited From: [\YooKassa\Request\PosLink\CreatePosLinkRequestInterface](../classes/YooKassa-Request-PosLink-CreatePosLinkRequestInterface.md)

##### Parameters:
| Type | Name | Description |
| ---- | ---- | ----------- |
| <code lang="php">\YooKassa\Request\PosLink\PosLinkData OR array OR null</code> | pos_link_data  | Данные кассовой ссылки |

**Returns:** self - 




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