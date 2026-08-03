# [YooKassa API SDK](../home.md)

# Class: \YooKassa\Request\PaymentMethods\PaymentMethodResponseFactory
### Namespace: [\YooKassa\Request\PaymentMethods](../namespaces/yookassa-request-paymentmethods.md)
---
**Summary:**

Класс, представляющий модель PaymentMethodResponseFactory.

**Description:**

Фабрика создания объекта способа оплаты из массива.

---
### Constants
* No constants found

---
### Methods
| Visibility | Name | Flag | Summary |
| ----------:| ---- | ---- | ------- |
| public | [factory()](../classes/YooKassa-Request-PaymentMethods-PaymentMethodResponseFactory.md#method_factory) |  | Фабричный метод создания объекта способа оплаты по коду способа оплаты. |
| public | [factoryFromArray()](../classes/YooKassa-Request-PaymentMethods-PaymentMethodResponseFactory.md#method_factoryFromArray) |  | Фабричный метод создания объекта способа оплаты из массива. |

---
### Details
* File: [lib/Request/PaymentMethods/PaymentMethodResponseFactory.php](../../lib/Request/PaymentMethods/PaymentMethodResponseFactory.php)
* Package: YooKassa\Model
* Class Hierarchy:
  * \YooKassa\Request\PaymentMethods\PaymentMethodResponseFactory

* See Also:
  * [](https://yookassa.ru/developers/api)

---
### Tags
| Tag | Version | Description |
| --- | ------- | ----------- |
| category |  | Class |
| author |  | cms@yoomoney.ru |

---
## Methods
<a name="method_factory" class="anchor"></a>
#### public factory() : \YooKassa\Request\PaymentMethods\PaymentMethodResponseInterface

```php
public factory(string|null $type) : \YooKassa\Request\PaymentMethods\PaymentMethodResponseInterface
```

**Summary**

Фабричный метод создания объекта способа оплаты по коду способа оплаты.

**Details:**
* Inherited From: [\YooKassa\Request\PaymentMethods\PaymentMethodResponseFactory](../classes/YooKassa-Request-PaymentMethods-PaymentMethodResponseFactory.md)

##### Parameters:
| Type | Name | Description |
| ---- | ---- | ----------- |
| <code lang="php">string OR null</code> | type  | Код способа оплаты |

**Returns:** \YooKassa\Request\PaymentMethods\PaymentMethodResponseInterface - 


<a name="method_factoryFromArray" class="anchor"></a>
#### public factoryFromArray() : \YooKassa\Request\PaymentMethods\PaymentMethodResponseInterface

```php
public factoryFromArray(array $data, null|string $type = null) : \YooKassa\Request\PaymentMethods\PaymentMethodResponseInterface
```

**Summary**

Фабричный метод создания объекта способа оплаты из массива.

**Details:**
* Inherited From: [\YooKassa\Request\PaymentMethods\PaymentMethodResponseFactory](../classes/YooKassa-Request-PaymentMethods-PaymentMethodResponseFactory.md)

##### Parameters:
| Type | Name | Description |
| ---- | ---- | ----------- |
| <code lang="php">array</code> | data  | Массив данных способа оплаты |
| <code lang="php">null OR string</code> | type  | Коду способа оплаты |

**Returns:** \YooKassa\Request\PaymentMethods\PaymentMethodResponseInterface - 



---

### Top Namespaces

* [\YooKassa](../namespaces/yookassa.md)

---

### Reports
* [Errors - 0](../reports/errors.md)
* [Markers - 0](../reports/markers.md)
* [Deprecated - 43](../reports/deprecated.md)

---

This document was automatically generated from source code comments on 2026-06-29 using [phpDocumentor](http://www.phpdoc.org/)

&copy; 2026 YooMoney