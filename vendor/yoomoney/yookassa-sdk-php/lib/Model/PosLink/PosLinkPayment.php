<?php

/*
 * The MIT License
 *
 * Copyright (c) 2026 "YooMoney", NBСO LLC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace YooKassa\Model\PosLink;

use DateTime;
use YooKassa\Common\AbstractObject;
use YooKassa\Validator\Constraints as Assert;

/**
 * Класс, представляющий модель PaymentData.
 *
 * Данные о кассовой ссылке для проведения платежа в офлайне.
 *
 * @category Class
 * @package  YooKassa\Model
 * @author   cms@yoomoney.ru
 * @link     https://yookassa.ru/developers/api
 *
 * @property string $id Идентификатор кассовой ссылки в ЮKassa
 * @property DateTime $expiresAt Срок действия оплаты
 * @property DateTime $expires_at Срок действия оплаты
 */
class PosLinkPayment extends AbstractObject
{
    /**
     * Идентификатор кассовой ссылки в ЮKassa.
     *
     * @var string|null
     */
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(max: 50)]
    #[Assert\Length(min: 36)]
    protected ?string $_id = null;

    /**
     * Срок действия оплаты. Может составлять от 1 до 20 минут с момента создания платежа, по умолчанию 20 минут.
     *
     * @var DateTime|null
     */
    #[Assert\NotBlank]
    #[Assert\DateTime(format: YOOKASSA_DATE)]
    #[Assert\Type('DateTime')]
    protected ?DateTime $_expires_at = null;

    /**
     * Возвращает идентификатор кассовой ссылки в ЮKassa.
     *
     * @return string|null Идентификатор кассовой ссылки в ЮKassa
     */
    public function getId(): ?string
    {
        return $this->_id;
    }

    /**
     * Устанавливает идентификатор кассовой ссылки в ЮKassa.
     *
     * @param string|null $id Идентификатор кассовой ссылки в ЮKassa
     *
     * @return self
     */
    public function setId(?string $id = null): self
    {
        $this->_id = $this->validatePropertyValue('_id', $id);
        return $this;
    }

    /**
     * Возвращает срок действия оплаты.
     *
     * @return DateTime|null Срок действия оплаты
     */
    public function getExpiresAt(): ?DateTime
    {
        return $this->_expires_at;
    }

    /**
     * Устанавливает срок действия оплаты.
     *
     * @param DateTime|string|null $expires_at Срок действия оплаты
     *
     * @return self
     */
    public function setExpiresAt(DateTime|string|null $expires_at = null): self
    {
        $this->_expires_at = $this->validatePropertyValue('_expires_at', $expires_at);
        return $this;
    }
}
