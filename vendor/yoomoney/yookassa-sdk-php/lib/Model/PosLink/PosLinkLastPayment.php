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
use YooKassa\Model\Payment\PaymentStatus;
use YooKassa\Validator\Constraints as Assert;

/**
 * Класс, представляющий модель PosLinkLastPayment.
 *
 * Данные о последнем платеже по кассовой ссылке.
 *
 * @category Class
 * @package  YooKassa\Model
 * @author   cms@yoomoney.ru
 * @link     https://yookassa.ru/developers/api
 *
 * @property string $id Идентификатор платежа в ЮKassa
 * @property string $status Текущее состояние платежа
 */
class PosLinkLastPayment extends AbstractObject
{
    /**
     * Идентификатор платежа в ЮKassa.
     *
     * @var string|null
     */
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(max: 36)]
    #[Assert\Length(min: 36)]
    protected ?string $_id = null;

    /**
     * @var string|null Текущее состояние платежа
     */
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Choice(callback: [PaymentStatus::class, 'getValidValues'])]
    protected ?string $_status = null;

    /**
     * Возвращает идентификатор платежа в ЮKassa.
     *
     * @return string|null Идентификатор платежа в ЮKassa
     */
    public function getId(): ?string
    {
        return $this->_id;
    }

    /**
     * Устанавливает идентификатор платежа в ЮKassa.
     *
     * @param string|null $id Идентификатор платежа в ЮKassa
     *
     * @return self
     */
    public function setId(?string $id = null): self
    {
        $this->_id = $this->validatePropertyValue('_id', $id);
        return $this;
    }

    /**
     * Возвращает состояние платежа.
     *
     * @return string|null Текущее состояние платежа
     */
    public function getStatus(): ?string
    {
        return $this->_status;
    }

    /**
     * Устанавливает состояние платежа.
     *
     * @param string|null $status Текущее состояние платежа
     *
     * @return self
     */
    public function setStatus(?string $status = null): self
    {
        $this->_status = $this->validatePropertyValue('_status', $status);
        return $this;
    }
}
