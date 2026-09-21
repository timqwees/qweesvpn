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
use YooKassa\Request\PosLink\CreatePosLinkRequestSerializer;
use YooKassa\Request\PosLink\CreatePosLinkRequest;

/**
 * CreatePosLinkRequestSerializerTest
 *
 * @category    ClassTest
 * @author      cms@yoomoney.ru
 * @link        https://yookassa.ru/developers/api
 */
class CreatePosLinkRequestSerializerTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     *
     * @param mixed $options
     */
    public function testSerialize(mixed $options): void
    {
        $serializer = new CreatePosLinkRequestSerializer();
        $instance = CreatePosLinkRequest::builder()->build($options);
        $data = $serializer->serialize($instance);

        $expected = [
            'recipient' => $options['recipient'],
            'pos_link_data' => $options['pos_link_data'],
        ];

        self::assertEquals($expected, $data);
    }

    public static function validDataProvider(): array
    {
        return [
            [
                [
                    'recipient' => [
                        'gateway_id' => '123456',
                    ],
                    'pos_link_data' => [
                        'link' => 'https://shop.example.ru/pay/1234567890',
                    ],
                ],
            ],
        ];
    }
}
