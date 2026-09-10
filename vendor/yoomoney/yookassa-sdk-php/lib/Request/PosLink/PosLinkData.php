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

use YooKassa\Common\AbstractObject;
use YooKassa\Validator\Constraints as Assert;

/**
 * Класс, представляющий модель PosLinkData.
 *
 * Данные кассовой ссылки для создания.
 *
 * @category Class
 * @package  YooKassa\Request
 * @author   cms@yoomoney.ru
 * @link     https://yookassa.ru/developers/api
 *
 * @property string $link Кассовая ссылка с платежной таблички
 */
class PosLinkData extends AbstractObject
{
    /**
     * Кассовая ссылка с платежной таблички. Чтобы получить ее, отсканируйте QR-код на табличке.
     *
     * @var string|null
     */
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(max: 255)]
    #[Assert\Length(min: 10)]
    protected ?string $_link = null;

    /**
     * Возвращает кассовую ссылку с платежной таблички.
     *
     * @return string|null Кассовая ссылка с платежной таблички
     */
    public function getLink(): ?string
    {
        return $this->_link;
    }

    /**
     * Устанавливает кассовую ссылку с платежной таблички.
     *
     * @param string|null $link Кассовая ссылка с платежной таблички
     *
     * @return self
     */
    public function setLink(?string $link = null): self
    {
        $this->_link = $this->validatePropertyValue('_link', $link);
        return $this;
    }
}
