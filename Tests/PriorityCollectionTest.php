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
 * PriorityCollectionTest.php
 * php-collection
 *
 * Created on 2019-04-04 22:06 by thomas
 */

use TASoft\Collection\PriorityCollection;
use PHPUnit\Framework\TestCase;

class PriorityCollectionTest extends TestCase
{
    public function testConstruction() {
        $collection = new PriorityCollection();
        $this->assertEmpty($collection);

        $collection = new PriorityCollection(3, [1, 2, 3]);
        $this->assertCount(3, $collection);

        $this->assertEquals([1, 2, 3], $collection->toArray());
    }

    public function testAdd()
    {
        $collection = new PriorityCollection();
        $collection->add(3, [5, 7]);
        $collection->add(1, 8, 7);

        $this->assertEquals([8, 7, [5, 7]], $collection->toArray());
    }

    public function testRemove()
    {
        $collection = new PriorityCollection(5, [1, 2, 3]);
        $collection->remove(2);

        $this->assertCount(2, $collection);
        $this->assertEquals([1, 3], array_values($collection->toArray()));
    }
}
