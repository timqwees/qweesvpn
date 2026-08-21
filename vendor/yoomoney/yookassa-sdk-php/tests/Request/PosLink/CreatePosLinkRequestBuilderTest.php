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

use PHPUnit\Framework\TestCase;
use YooKassa\Helpers\Random;
use YooKassa\Request\PosLink\CreatePosLinkRequest;
use YooKassa\Request\PosLink\CreatePosLinkRequestBuilder;

/**
 * CreatePosLinkRequestBuilderTest
 *
 * @category    ClassTest
 * @author      cms@yoomoney.ru
 * @link        https://yookassa.ru/developers/api
 */
class CreatePosLinkRequestBuilderTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     *
     * @param mixed $options
     */
    public function testSetRecipient(mixed $options): void
    {
        $builder = CreatePosLinkRequest::builder();
        $builder->setRecipient($options['recipient']);
        $builder->setPosLinkData($options['pos_link_data']);
        $instance = $builder->build();

        self::assertNotNull($instance->getRecipient());
        self::assertEquals($options['recipient'], $instance->getRecipient()->toArray());
    }

    /**
     * @dataProvider validDataProvider
     *
     * @param mixed $options
     */
    public function testSetPosLinkData(mixed $options): void
    {
        $builder = CreatePosLinkRequest::builder();
        $builder->setRecipient($options['recipient']);
        $builder->setPosLinkData($options['pos_link_data']);
        $instance = $builder->build();

        self::assertNotNull($instance->getPosLinkData());
        self::assertEquals($options['pos_link_data'], $instance->getPosLinkData()->toArray());
    }

    public function testBuilder(): void
    {
        $builder = CreatePosLinkRequest::builder();
        self::assertInstanceOf(CreatePosLinkRequestBuilder::class, $builder);
    }

    public static function validDataProvider(): array
    {
        $result = [];
        for ($i = 0; $i < 10; $i++) {
            $request = [
                'recipient' => [
                    'gateway_id' => Random::str(1, 128, '0123456789'),
                ],
                'pos_link_data' => [
                    'link' => Random::str(10, 255),
                ],
            ];
            $result[] = [$request];
        }

        return $result;
    }
}
