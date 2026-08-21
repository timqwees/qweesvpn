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

use YooKassa\Common\AbstractObject;
use YooKassa\Validator\Constraints as Assert;

/**
 * Класс, представляющий модель PosLinkInfo.
 *
 * Информация о кассовой ссылке.
 *
 * @category Class
 * @package  YooKassa\Model
 * @author   cms@yoomoney.ru
 * @link     https://yookassa.ru/developers/api
 *
 * @property string $id Идентификатор кассовой ссылки в ЮKassa
 * @property string $status Статус кассовой ссылки
 * @property string $type Тип кассовой ссылки
 * @property PosLinkRecipient $recipient Получатель платежа по кассовой ссылке
 * @property PosLinkLastPayment $payment Данные о последнем платеже по кассовой ссылке
 */
class PosLinkInfo extends AbstractObject
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
     * @var string|null Статус кассовой ссылки
     */
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Choice(callback: [PosLinkStatus::class, 'getValidValues'])]
    protected ?string $_status = null;

    /**
     * @var string|null Тип кассовой ссылки
     */
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Choice(callback: [PosLinkType::class, 'getValidValues'])]
    protected ?string $_type = null;

    /**
     * @var PosLinkRecipient|null Получатель платежа по кассовой ссылке
     */
    #[Assert\NotBlank]
    #[Assert\Valid]
    #[Assert\Type(PosLinkRecipient::class)]
    protected ?PosLinkRecipient $_recipient = null;

    /**
     * @var PosLinkLastPayment|null Данные о последнем платеже по кассовой ссылке
     */
    #[Assert\Valid]
    #[Assert\Type(PosLinkLastPayment::class)]
    protected ?PosLinkLastPayment $_payment = null;

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
     * Возвращает статус кассовой ссылки.
     *
     * @return string|null Статус кассовой ссылки
     */
    public function getStatus(): ?string
    {
        return $this->_status;
    }

    /**
     * Устанавливает статус кассовой ссылки.
     *
     * @param string|null $status Статус кассовой ссылки
     *
     * @return self
     */
    public function setStatus(?string $status = null): self
    {
        $this->_status = $this->validatePropertyValue('_status', $status);
        return $this;
    }

    /**
     * Возвращает тип кассовой ссылки.
     *
     * @return string|null Тип кассовой ссылки
     */
    public function getType(): ?string
    {
        return $this->_type;
    }

    /**
     * Устанавливает тип кассовой ссылки.
     *
     * @param string|null $type Тип кассовой ссылки
     *
     * @return self
     */
    public function setType(?string $type = null): self
    {
        $this->_type = $this->validatePropertyValue('_type', $type);
        return $this;
    }

    /**
     * Возвращает получателя платежа по кассовой ссылке.
     *
     * @return PosLinkRecipient|null Получатель платежа по кассовой ссылке
     */
    public function getRecipient(): ?PosLinkRecipient
    {
        return $this->_recipient;
    }

    /**
     * Устанавливает получателя платежа по кассовой ссылке.
     *
     * @param PosLinkRecipient|array|null $recipient Получатель платежа по кассовой ссылке
     *
     * @return self
     */
    public function setRecipient(mixed $recipient = null): self
    {
        $this->_recipient = $this->validatePropertyValue('_recipient', $recipient);
        return $this;
    }

    /**
     * Возвращает данные о последнем платеже по кассовой ссылке.
     *
     * @return PosLinkLastPayment|null Данные о последнем платеже по кассовой ссылке
     */
    public function getPayment(): ?PosLinkLastPayment
    {
        return $this->_payment;
    }

    /**
     * Устанавливает данные о последнем платеже по кассовой ссылке.
     *
     * @param PosLinkLastPayment|array|null $payment Данные о последнем платеже по кассовой ссылке
     *
     * @return self
     */
    public function setPayment(mixed $payment = null): self
    {
        $this->_payment = $this->validatePropertyValue('_payment', $payment);
        return $this;
    }
}
