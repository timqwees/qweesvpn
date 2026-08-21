# [YooKassa API SDK](../home.md)

# Interface: RecipientPosLinkRequestInterface
### Namespace: [\YooKassa\Request\PosLink](../namespaces/yookassa-request-poslink.md)
---
**Summary:**

Interface RecipientPosLinkRequestInterface.

---
### Constants
* No constants found

---
### Methods
| Visibility | Name | Flag | Summary |
| ----------:| ---- | ---- | ------- |
| public | [getRecipient()](../classes/YooKassa-Request-PosLink-RecipientPosLinkRequestInterface.md#method_getRecipient) |  | Возвращает получателя платежа по кассовой ссылке. |
| public | [setRecipient()](../classes/YooKassa-Request-PosLink-RecipientPosLinkRequestInterface.md#method_setRecipient) |  | Устанавливает получателя платежа по кассовой ссылке. |

---
### Details
* File: [lib/Request/PosLink/RecipientPosLinkRequestInterface.php](../../lib/Request/PosLink/RecipientPosLinkRequestInterface.php)
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
* Inherited From: [\YooKassa\Request\PosLink\RecipientPosLinkRequestInterface](../classes/YooKassa-Request-PosLink-RecipientPosLinkRequestInterface.md)

**Returns:** \YooKassa\Model\PosLink\PosLinkRecipient|null - Получатель платежа по кассовой ссылке


<a name="method_setRecipient" class="anchor"></a>
#### public setRecipient() : self

```php
public setRecipient(\YooKassa\Model\PosLink\PosLinkRecipient|array|null $recipient = null) : self
```

**Summary**

Устанавливает получателя платежа по кассовой ссылке.

**Details:**
* Inherited From: [\YooKassa\Request\PosLink\RecipientPosLinkRequestInterface](../classes/YooKassa-Request-PosLink-RecipientPosLinkRequestInterface.md)

##### Parameters:
| Type | Name | Description |
| ---- | ---- | ----------- |
| <code lang="php">\YooKassa\Model\PosLink\PosLinkRecipient OR array OR null</code> | recipient  | Получатель платежа по кассовой ссылке |

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