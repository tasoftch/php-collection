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
 * TaggedCollectionTest.php
 * php-collection
 *
 * Created on 2019-04-06 00:11 by thomas
 */

use PHPUnit\Framework\TestCase;
use TASoft\Collection\TaggedCollection;

class TaggedCollectionTest extends TestCase
{
    public function testConstruction() {
        $collection = new TaggedCollection([1, 2, 3], true, true,"test", "haha");
        $this->assertCount(3, $collection);


        $this->assertEquals([1, 2, 3], $collection["test"]);
        $this->assertEquals([1, 2, 3], $collection["haha"]);

        $this->assertNull($collection["unexisting tag"]);
    }

    public function testAdding() {
        $collection = new TaggedCollection([1, 2], true, true,"test", "haha");
        $collection->add(56, "hehe", "test");
        $collection->add(13, "haha", "hehe");

        $this->assertEquals([1, 2, 56], $collection["test"]);
        $this->assertTrue($collection->contains(56));

        $this->assertEquals([56, 13], $collection["hehe"]);
    }

    public function testRemovingElements() {
        $collection = new TaggedCollection([1, 2, 3], true, true,"test", "haha");
        $collection->add(56, "hehe", "test");
        $collection->add(13, "haha", "hehe");

        $this->assertCount(5, $collection);

        $collection->remove(2);
        $this->assertCount(4, $collection);

        $this->assertEquals([1, 3, 56], $collection["test"]);
    }

    public function testRemovingTags() {
        $collection = new TaggedCollection([1, 2, 3], true, true,"test", "haha");
        $collection->add(56, "hehe", "test");
        $collection->add(13, "haha", "hehe");

        $this->assertCount(5, $collection);

        $collection->removeTags('hehe', 'test');

        $this->assertEquals([1, 2, 3, 13], array_values($collection->toArray()));

        $this->assertNull($collection["hehe"]);
    }
}
