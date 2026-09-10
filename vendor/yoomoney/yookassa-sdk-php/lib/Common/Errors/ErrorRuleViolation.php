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


namespace YooKassa\Common\Errors;

use YooKassa\Validator\Constraints as Assert;

/**
 * Класс, представляющий модель ErrorRefusal.
 *
 * Запрос не может быть выполнен согласно правилам бизнес-логики.
 * 
 * @category Class
 * @package  YooKassa\Model
 * @author   cms@yoomoney.ru
 * @link     https://yookassa.ru/developers/api
 * @property string $reason Причина по которой запрос не может быть выполнен по правилам бизнес-логики.
*/
class ErrorRuleViolation extends AbstractError
{
    public function __construct(?array $data = [])
    {
        parent::__construct($data);
        $this->setCode(ErrorCode::REFUSAL);
    }

    /**
     * Причина по которой запрос не может быть выполнен по правилам бизнес-логики.
     *
     * @var string|null
     */
    #[Assert\Type('string')]
    private ?string $_reason = null;

    /**
     * Возвращает reason.
     *
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->_reason;
    }

    /**
     * Устанавливает reason.
     *
     * @param string|null $reason Причина по которой запрос не может быть выполнен по правилам бизнес-логики.
     *
     * @return self
     */
    public function setReason(?string $reason = null): self
    {
        $this->_reason = $this->validatePropertyValue('_reason', $reason);
        return $this;
    }
}

