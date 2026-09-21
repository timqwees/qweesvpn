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


namespace YooKassa\Model\SavePaymentMethod;

use YooKassa\Validator\Constraints as Assert;

/**
 * Класс, представляющий модель SavePaymentMethodSbp.
 *
 * Сохраненный счет СБП.
 * 
 * @category Class
 * @package  YooKassa\Model
 * @author   cms@yoomoney.ru
 * @link     https://yookassa.ru/developers/api
 * @property SavePaymentMethodSbpPayerBankDetails $payer_bank_details 
 * @property SavePaymentMethodSbpPayerBankDetails $payerBankDetails
*/
class SavePaymentMethodSbp extends AbstractSavePaymentMethod
{
    /**
     * @var SavePaymentMethodSbpPayerBankDetails|null
     */
    #[Assert\Type(SavePaymentMethodSbpPayerBankDetails::class)]
    private ?SavePaymentMethodSbpPayerBankDetails $_payer_bank_details = null;

    public function __construct(?array $data = [])
    {
        parent::__construct($data);
        $this->setType(SavePaymentMethodType::SBP);
    }

    /**
     * Возвращает payer_bank_details.
     *
     * @return SavePaymentMethodSbpPayerBankDetails|null
     */
    public function getPayerBankDetails(): ?SavePaymentMethodSbpPayerBankDetails
    {
        return $this->_payer_bank_details;
    }

    /**
     * Устанавливает payer_bank_details.
     *
     * @param SavePaymentMethodSbpPayerBankDetails|array|null $payer_bank_details
     *
     * @return self
     */
    public function setPayerBankDetails(mixed $payer_bank_details = null): self
    {
        $this->_payer_bank_details = $this->validatePropertyValue('_payer_bank_details', $payer_bank_details);
        return $this;
    }

}

