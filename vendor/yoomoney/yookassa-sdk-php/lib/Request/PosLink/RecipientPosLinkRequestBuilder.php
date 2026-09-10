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

use YooKassa\Common\AbstractRequestBuilder;
use YooKassa\Common\AbstractRequestInterface;

/**
 * Класс, представляющий модель RecipientPosLinkRequestBuilder.
 *
 * Класс билдера объектов запросов на привязку получателя к кассовой ссылке.
 *
 * @category Class
 * @package  YooKassa\Request
 * @author   cms@yoomoney.ru
 * @link     https://yookassa.ru/developers/api
 */
class RecipientPosLinkRequestBuilder extends AbstractRequestBuilder
{
    /**
     * Собираемый объект запроса.
     *
     * @var RecipientPosLinkRequest|null
     */
    protected ?AbstractRequestInterface $currentObject = null;

    /**
     * Устанавливает получателя платежа по кассовой ссылке.
     *
     * @param mixed $value Получатель платежа по кассовой ссылке
     *
     * @return RecipientPosLinkRequestBuilder Инстанс текущего билдера
     */
    public function setRecipient(mixed $value): RecipientPosLinkRequestBuilder
    {
        $this->currentObject->setRecipient($value);

        return $this;
    }

    /**
     * Осуществляет сборку объекта запроса к API.
     *
     * @param array|null $options
     *
     * @return RecipientPosLinkRequestInterface|AbstractRequestInterface
     */
    public function build(?array $options = null): AbstractRequestInterface
    {
        return parent::build($options);
    }

    /**
     * Инициализирует объект запроса, который в дальнейшем будет собираться билдером.
     *
     * @return RecipientPosLinkRequest Инстанс собираемого объекта запроса к API
     */
    protected function initCurrentObject(): RecipientPosLinkRequest
    {
        return new RecipientPosLinkRequest();
    }
}