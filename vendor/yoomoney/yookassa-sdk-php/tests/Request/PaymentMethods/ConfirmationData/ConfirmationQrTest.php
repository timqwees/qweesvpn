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


namespace Tests\YooKassa\Request\PaymentMethods\ConfirmationData;

use Exception;
use Tests\YooKassa\AbstractTestCase;
use YooKassa\Request\PaymentMethods\ConfirmationData\ConfirmationQr;

/**
 * ConfirmationQrTest
 *
 * @category    ClassTest
 * @author      cms@yoomoney.ru
 * @link        https://yookassa.ru/developers/api
*/
class ConfirmationQrTest extends AbstractTestCase
{
    protected ConfirmationQr $object;

    /**
    * @return ConfirmationQr
    */
    protected function getTestInstance(): ConfirmationQr
    {
        return new ConfirmationQr();
    }

    /**
    * @return void
    */
    public function testConfirmationQrClassExists(): void
    {
        $this->object = $this->getMockBuilder(ConfirmationQr::class)->getMockForAbstractClass();
        $this->assertTrue(class_exists(ConfirmationQr::class));
        $this->assertInstanceOf(ConfirmationQr::class, $this->object);
    }

    /**
     * Test property "type"
     *
     * @return void
     * @throws Exception
     */
    public function testType(): void
    {
        $instance = $this->getTestInstance();
        self::assertContains($instance->getType(), ['qr']);
        self::assertContains($instance->type, ['qr']);
        self::assertNotNull($instance->getType());
        self::assertNotNull($instance->type);
    }

    /**
    * Test property "return_url"
    * @dataProvider validReturnUrlDataProvider
    * @param mixed $value
    *
    * @return void
    * @throws Exception
    */
    public function testReturnUrl(mixed $value): void
    {
        $instance = $this->getTestInstance();
        self::assertEmpty($instance->getReturnUrl());
        self::assertEmpty($instance->return_url);
        $instance->setReturnUrl($value);
        self::assertEquals($value, is_array($value) ? $instance->getReturnUrl()->toArray() : $instance->getReturnUrl());
        self::assertEquals($value, is_array($value) ? $instance->return_url->toArray() : $instance->return_url);
        if (!empty($value)) {
            self::assertNotNull($instance->getReturnUrl());
            self::assertNotNull($instance->return_url);
        }
    }

    /**
    * Test invalid property "return_url"
    * @dataProvider invalidReturnUrlDataProvider
    * @param mixed $value
    * @param string $exceptionClass
    *
    * @return void
    */
    public function testInvalidReturnUrl(mixed $value, string $exceptionClass): void
    {
        $instance = $this->getTestInstance();

        $this->expectException($exceptionClass);
        $instance->setReturnUrl($value);
    }

    /**
    * @return array[]
    * @throws Exception
    */
    public function validReturnUrlDataProvider(): array
    {
        $instance = $this->getTestInstance();
        return $this->getValidDataProviderByType($instance->getValidator()->getRulesByPropName('_return_url'));
    }

    /**
    * @return array[]
    * @throws Exception
    */
    public function invalidReturnUrlDataProvider(): array
    {
        $instance = $this->getTestInstance();
        return $this->getInvalidDataProviderByType($instance->getValidator()->getRulesByPropName('_return_url'));
    }
}
