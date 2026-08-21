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

namespace Tests\YooKassa\Request\PosLink;

use Exception;
use Tests\YooKassa\AbstractTestCase;
use YooKassa\Request\PosLink\CreatePosLinkRequest;

/**
 * CreatePosLinkRequestTest
 *
 * @category    ClassTest
 * @author      cms@yoomoney.ru
 * @link        https://yookassa.ru/developers/api
 */
class CreatePosLinkRequestTest extends AbstractTestCase
{
    protected CreatePosLinkRequest $object;

    /**
     * @return CreatePosLinkRequest
     */
    protected function getTestInstance(): CreatePosLinkRequest
    {
        return new CreatePosLinkRequest();
    }

    /**
     * @return void
     */
    public function testCreatePosLinkRequestClassExists(): void
    {
        $this->object = $this->getMockBuilder(CreatePosLinkRequest::class)->getMockForAbstractClass();
        $this->assertTrue(class_exists(CreatePosLinkRequest::class));
        $this->assertInstanceOf(CreatePosLinkRequest::class, $this->object);
    }

    /**
     * Test property "recipient"
     * @dataProvider validRecipientDataProvider
     * @param mixed $value
     *
     * @return void
     * @throws Exception
     */
    public function testRecipient(mixed $value): void
    {
        $instance = $this->getTestInstance();
        $instance->setRecipient($value);
        self::assertNotNull($instance->getRecipient());
        self::assertEquals($value, is_array($value) ? $instance->getRecipient()->toArray() : $instance->getRecipient());
    }

    /**
     * Test invalid property "recipient"
     * @dataProvider invalidRecipientDataProvider
     * @param mixed $value
     * @param string $exceptionClass
     *
     * @return void
     */
    public function testInvalidRecipient(mixed $value, string $exceptionClass): void
    {
        $instance = $this->getTestInstance();

        $this->expectException($exceptionClass);
        $instance->setRecipient($value);
    }

    /**
     * @return array[]
     * @throws Exception
     */
    public function validRecipientDataProvider(): array
    {
        $instance = $this->getTestInstance();
        return $this->getValidDataProviderByType($instance->getValidator()->getRulesByPropName('_recipient'));
    }

    /**
     * @return array[]
     * @throws Exception
     */
    public function invalidRecipientDataProvider(): array
    {
        $instance = $this->getTestInstance();
        return $this->getInvalidDataProviderByType($instance->getValidator()->getRulesByPropName('_recipient'));
    }

    /**
     * Test property "pos_link_data"
     * @dataProvider validPosLinkDataDataProvider
     * @param mixed $value
     *
     * @return void
     * @throws Exception
     */
    public function testPosLinkData(mixed $value): void
    {
        $instance = $this->getTestInstance();
        $instance->setPosLinkData($value);
        self::assertNotNull($instance->getPosLinkData());
        self::assertEquals($value, is_array($value) ? $instance->getPosLinkData()->toArray() : $instance->getPosLinkData());
    }

    /**
     * Test invalid property "pos_link_data"
     * @dataProvider invalidPosLinkDataDataProvider
     * @param mixed $value
     * @param string $exceptionClass
     *
     * @return void
     */
    public function testInvalidPosLinkData(mixed $value, string $exceptionClass): void
    {
        $instance = $this->getTestInstance();

        $this->expectException($exceptionClass);
        $instance->setPosLinkData($value);
    }

    /**
     * @return array[]
     * @throws Exception
     */
    public function validPosLinkDataDataProvider(): array
    {
        $instance = $this->getTestInstance();
        return $this->getValidDataProviderByType($instance->getValidator()->getRulesByPropName('_pos_link_data'));
    }

    /**
     * @return array[]
     * @throws Exception
     */
    public function invalidPosLinkDataDataProvider(): array
    {
        $instance = $this->getTestInstance();
        return $this->getInvalidDataProviderByType($instance->getValidator()->getRulesByPropName('_pos_link_data'));
    }
}
