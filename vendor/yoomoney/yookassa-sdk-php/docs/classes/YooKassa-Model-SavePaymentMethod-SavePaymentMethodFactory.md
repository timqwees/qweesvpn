# [YooKassa API SDK](../home.md)

# Class: \YooKassa\Model\SavePaymentMethod\SavePaymentMethodFactory
### Namespace: [\YooKassa\Model\SavePaymentMethod](../namespaces/yookassa-model-savepaymentmethod.md)
---
**Summary:**

Класс, представляющий модель SavePaymentMethodFactory.

**Description:**

Фабрика создания объекта способа оплаты из массива.

---
### Constants
* No constants found

---
### Methods
| Visibility | Name | Flag | Summary |
| ----------:| ---- | ---- | ------- |
| public | [factory()](../classes/YooKassa-Model-SavePaymentMethod-SavePaymentMethodFactory.md#method_factory) |  | Фабричный метод создания объекта способа оплаты по коду способа оплаты. |
| public | [factoryFromArray()](../classes/YooKassa-Model-SavePaymentMethod-SavePaymentMethodFactory.md#method_factoryFromArray) |  | Фабричный метод создания объекта способа оплаты из массива. |

---
### Details
* File: [lib/Model/SavePaymentMethod/SavePaymentMethodFactory.php](../../lib/Model/SavePaymentMethod/SavePaymentMethodFactory.php)
* Package: YooKassa\Model
* Class Hierarchy:
  * \YooKassa\Model\SavePaymentMethod\SavePaymentMethodFactory

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
#### public factory() : \YooKassa\Model\SavePaymentMethod\AbstractSavePaymentMethod

```php
public factory(string|null $type) : \YooKassa\Model\SavePaymentMethod\AbstractSavePaymentMethod
```

**Summary**

Фабричный метод создания объекта способа оплаты по коду способа оплаты.

**Details:**
* Inherited From: [\YooKassa\Model\SavePaymentMethod\SavePaymentMethodFactory](../classes/YooKassa-Model-SavePaymentMethod-SavePaymentMethodFactory.md)

##### Parameters:
| Type | Name | Description |
| ---- | ---- | ----------- |
| <code lang="php">string OR null</code> | type  | Код способа оплаты |

**Returns:** \YooKassa\Model\SavePaymentMethod\AbstractSavePaymentMethod - 


<a name="method_factoryFromArray" class="anchor"></a>
#### public factoryFromArray() : \YooKassa\Model\SavePaymentMethod\AbstractSavePaymentMethod

```php
public factoryFromArray(array $data, null|string $type = null) : \YooKassa\Model\SavePaymentMethod\AbstractSavePaymentMethod
```

**Summary**

Фабричный метод создания объекта способа оплаты из массива.

**Details:**
* Inherited From: [\YooKassa\Model\SavePaymentMethod\SavePaymentMethodFactory](../classes/YooKassa-Model-SavePaymentMethod-SavePaymentMethodFactory.md)

##### Parameters:
| Type | Name | Description |
| ---- | ---- | ----------- |
| <code lang="php">array</code> | data  | Массив данных способа оплаты |
| <code lang="php">null OR string</code> | type  | Коду способа оплаты |

**Returns:** \YooKassa\Model\SavePaymentMethod\AbstractSavePaymentMethod - 



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