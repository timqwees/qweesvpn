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


namespace Tests\YooKassa\Common\Errors;

use Exception;
use Tests\YooKassa\AbstractTestCase;
use YooKassa\Common\Errors\ErrorRuleViolation;

/**
 * ErrorRuleViolationTest
 *
 * @category    ClassTest
 * @author      cms@yoomoney.ru
 * @link        https://yookassa.ru/developers/api
*/
class ErrorRuleViolationTest extends AbstractTestCase
{
    protected ErrorRuleViolationTest $object;

    /**
    * @return ErrorRuleViolationTest
    */
    protected function getTestInstance(): ErrorRuleViolation
    {
        return new ErrorRuleViolation();
    }

    /**
    * @return void
    */
    public function testErrorRefusalTestClassExists(): void
    {
        $this->object = $this->getMockBuilder(ErrorRuleViolationTest::class)->getMockForAbstractClass();
        $this->assertTrue(class_exists(ErrorRuleViolationTest::class));
        $this->assertInstanceOf(ErrorRuleViolationTest::class, $this->object);
    }

    /**
    * Test property "code"
    *
    * @return void
    * @throws Exception
    */
    public function testCode(): void
    {
        $instance = $this->getTestInstance();
        self::assertNotNull($instance->getCode());
        self::assertNotNull($instance->code);
        self::assertContains($instance->getCode(), ['refusal']);
        self::assertContains($instance->code, ['refusal']);
    }

    /**
    * Test property "reason"
    * @dataProvider validReasonDataProvider
    * @param mixed $value
    *
    * @return void
    * @throws Exception
    */
    public function testReason(mixed $value): void
    {
        $instance = $this->getTestInstance();
        self::assertEmpty($instance->getReason());
        self::assertEmpty($instance->reason);
        $instance->setReason($value);
        self::assertEquals($value, is_array($value) ? $instance->getReason()->toArray() : $instance->getReason());
        self::assertEquals($value, is_array($value) ? $instance->reason->toArray() : $instance->reason);
        if (!empty($value)) {
            self::assertNotNull($instance->getReason());
            self::assertNotNull($instance->reason);
        }
    }

    /**
    * Test invalid property "reason"
    * @dataProvider invalidReasonDataProvider
    * @param mixed $value
    * @param string $exceptionClass
    *
    * @return void
    */
    public function testInvalidReason(mixed $value, string $exceptionClass): void
    {
        $instance = $this->getTestInstance();

        $this->expectException($exceptionClass);
        $instance->setReason($value);
    }

    /**
    * @return array[]
    * @throws Exception
    */
    public function validReasonDataProvider(): array
    {
        $instance = $this->getTestInstance();
        return $this->getValidDataProviderByType($instance->getValidator()->getRulesByPropName('_reason'));
    }

    /**
    * @return array[]
    * @throws Exception
    */
    public function invalidReasonDataProvider(): array
    {
        $instance = $this->getTestInstance();
        return $this->getInvalidDataProviderByType($instance->getValidator()->getRulesByPropName('_reason'));
    }
}
