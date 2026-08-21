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

namespace Tests\YooKassa\Model\PosLink;

use Exception;
use Tests\YooKassa\AbstractTestCase;
use YooKassa\Model\Payment\PaymentStatus;
use YooKassa\Model\PosLink\PosLinkLastPayment;

/**
 * PosLinkLastPaymentTest
 *
 * @category    ClassTest
 * @author      cms@yoomoney.ru
 * @link        https://yookassa.ru/developers/api
 */
class PosLinkLastPaymentTest extends AbstractTestCase
{
    protected PosLinkLastPayment $object;

    /**
     * @return PosLinkLastPayment
     */
    protected function getTestInstance(): PosLinkLastPayment
    {
        return new PosLinkLastPayment();
    }

    /**
     * @return void
     */
    public function testPosLinkLastPaymentClassExists(): void
    {
        $this->object = $this->getMockBuilder(PosLinkLastPayment::class)->getMockForAbstractClass();
        $this->assertTrue(class_exists(PosLinkLastPayment::class));
        $this->assertInstanceOf(PosLinkLastPayment::class, $this->object);
    }

    /**
     * Test property "id"
     * @dataProvider validIdDataProvider
     * @param mixed $value
     *
     * @return void
     * @throws Exception
     */
    public function testId(mixed $value): void
    {
        $instance = $this->getTestInstance();
        $instance->setId($value);
        self::assertNotNull($instance->getId());
        self::assertNotNull($instance->id);
        self::assertEquals($value, $instance->getId());
        self::assertEquals($value, $instance->id);
    }

    /**
     * Test invalid property "id"
     * @dataProvider invalidIdDataProvider
     * @param mixed $value
     * @param string $exceptionClass
     *
     * @return void
     */
    public function testInvalidId(mixed $value, string $exceptionClass): void
    {
        $instance = $this->getTestInstance();

        $this->expectException($exceptionClass);
        $instance->setId($value);
    }

    /**
     * @return array[]
     * @throws Exception
     */
    public function validIdDataProvider(): array
    {
        $instance = $this->getTestInstance();
        return $this->getValidDataProviderByType($instance->getValidator()->getRulesByPropName('_id'));
    }

    /**
     * @return array[]
     * @throws Exception
     */
    public function invalidIdDataProvider(): array
    {
        $instance = $this->getTestInstance();
        return $this->getInvalidDataProviderByType($instance->getValidator()->getRulesByPropName('_id'));
    }

    /**
     * Test property "status"
     * @dataProvider validStatusDataProvider
     * @param mixed $value
     *
     * @return void
     * @throws Exception
     */
    public function testStatus(mixed $value): void
    {
        $instance = $this->getTestInstance();
        $instance->setStatus($value);
        self::assertNotNull($instance->getStatus());
        self::assertNotNull($instance->status);
        self::assertEquals($value, $instance->getStatus());
        self::assertEquals($value, $instance->status);
        self::assertContains($instance->getStatus(), PaymentStatus::getValidValues());
    }

    /**
     * Test invalid property "status"
     * @dataProvider invalidStatusDataProvider
     * @param mixed $value
     * @param string $exceptionClass
     *
     * @return void
     */
    public function testInvalidStatus(mixed $value, string $exceptionClass): void
    {
        $instance = $this->getTestInstance();

        $this->expectException($exceptionClass);
        $instance->setStatus($value);
    }

    /**
     * @return array[]
     * @throws Exception
     */
    public function validStatusDataProvider(): array
    {
        $instance = $this->getTestInstance();
        return $this->getValidDataProviderByType($instance->getValidator()->getRulesByPropName('_status'));
    }

    /**
     * @return array[]
     * @throws Exception
     */
    public function invalidStatusDataProvider(): array
    {
        $instance = $this->getTestInstance();
        return $this->getInvalidDataProviderByType($instance->getValidator()->getRulesByPropName('_status'));
    }
}
