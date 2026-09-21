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

namespace YooKassa\Request\PosLink;

use YooKassa\Common\AbstractRequest;
use YooKassa\Model\PosLink\PosLinkRecipient;
use YooKassa\Request\PosLink\PosLinkData;
use YooKassa\Validator\Constraints as Assert;

/**
 * Класс, представляющий модель CreatePosLinkRequest.
 *
 * Объект запроса к API на создание кассовой ссылки.
 *
 * @category Class
 * @package  YooKassa\Request
 * @author   cms@yoomoney.ru
 * @link     https://yookassa.ru/developers/api
 *
 * @property PosLinkRecipient $recipient Получатель платежа по кассовой ссылке
 * @property PosLinkData $pos_link_data Данные кассовой ссылки
 */
class CreatePosLinkRequest extends AbstractRequest implements CreatePosLinkRequestInterface
{
    /**
     * @var PosLinkRecipient|null Получатель платежа по кассовой ссылке
     */
    #[Assert\NotBlank]
    #[Assert\Valid]
    #[Assert\Type(PosLinkRecipient::class)]
    private ?PosLinkRecipient $_recipient = null;

    /**
     * @var PosLinkData|null Данные кассовой ссылки
     */
    #[Assert\NotBlank]
    #[Assert\Valid]
    #[Assert\Type(PosLinkData::class)]
    private ?PosLinkData $_pos_link_data = null;

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
     * Возвращает данные кассовой ссылки.
     *
     * @return PosLinkData|null Данные кассовой ссылки
     */
    public function getPosLinkData(): ?PosLinkData
    {
        return $this->_pos_link_data;
    }

    /**
     * Устанавливает данные кассовой ссылки.
     *
     * @param PosLinkData|array|null $pos_link_data Данные кассовой ссылки
     *
     * @return self
     */
    public function setPosLinkData(mixed $pos_link_data = null): self
    {
        $this->_pos_link_data = $this->validatePropertyValue('_pos_link_data', $pos_link_data);
        return $this;
    }

    /**
     * Проверяет на валидность текущий объект.
     *
     * @return bool True если объект запроса валиден, false если нет
     */
    public function validate(): bool
    {
        if (null === $this->_recipient) {
            $this->setValidationError('Recipient field is required');

            return false;
        }
        if (null === $this->_pos_link_data) {
            $this->setValidationError('PosLinkData field is required');

            return false;
        }

        return true;
    }

    /**
     * Возвращает билдер объектов запросов на создание кассовой ссылки.
     *
     * @return CreatePosLinkRequestBuilder Инстанс билдера объектов запросов
     */
    public static function builder(): CreatePosLinkRequestBuilder
    {
        return new CreatePosLinkRequestBuilder();
    }
}
