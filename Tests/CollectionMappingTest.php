<?php
/**
 * Copyright (c) 2019 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * CollectionMappingTest.php
 * php-collection
 *
 * Created on 2019-04-07 17:46 by thomas
 */

use PHPUnit\Framework\TestCase;
use TASoft\Collection\Mapping\CallbackMapper;
use TASoft\Collection\Mapping\CollectionMapping;
use TASoft\Collection\Mapping\RecursiveCallbackMapper;

class CollectionMappingTest extends TestCase
{
    public function testMapping() {
        $collection = [1, 2, 3];
        $map = new CollectionMapping(new CallbackMapper(function ($key, $value) {
            return $value * $value;
        }));

        $collection = $map->mapCollection($collection);
        $this->assertEquals([1, 4, 9], $collection);
    }

    public function testRecursiveMapping() {
        $collection = [
            1, 2, 3,
                [2, 3,
                    [4, 5, 6]
                ]
        ];
        $map = new CollectionMapping(new RecursiveCallbackMapper(function ($key, $value) {
            return $value * $value;
        }));

        $collection = $map->mapCollection($collection);
        $this->assertEquals([
            1, 4, 9,
            [4, 9,
                [16, 25, 36]
            ]
        ], $collection);
    }
}
