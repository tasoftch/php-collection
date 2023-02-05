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
 * AbstractContaineredCollectionTest.php
 * php-collection
 *
 * Created on 2019-04-05 17:26 by thomas
 */

use TASoft\Collection\AbstractContaineredCollection;
use PHPUnit\Framework\TestCase;
use TASoft\Collection\EqualObjectsTrait;
use TASoft\Collection\Exception\InvalidCollectionElementException;
use TASoft\Collection\StrictEqualObjectsTrait;
use TASoft\Collection\Element\PriorityCollectionElement;
use TASoft\Collection\PriorityCollection;
use TASoft\Collection\Element\ContainerElementInterface;

class AbstractContaineredCollectionTest extends TestCase
{

    public function testIndexOf()
    {
        $collection = new PriorityCollection(2, [1, 2, 3, 4, 6]);
        $this->assertEquals(2, $collection->indexOf(3));

        $this->assertNull($collection->indexOf(14));
    }

    public function testOffsetGet()
    {
        $collection = new PriorityCollection(2, [2, 5, [1, 4]]);
        $test = $collection[1];

        $this->assertEquals(5, $test);
    }

    public function testIndexesOf()
    {
        $collection = new PriorityCollection(2, [1, 2, 3, 4, 6, 8, 3, 6, 7, 4, 3]);
        $this->assertEquals([2, 6, 10], $collection->indexesOf(3));
    }

    public function testIterableConstructor()
    {
        $collection = new class(new ArrayIterator([1, 2, 3])) extends AbstractContaineredCollection {
            protected function getContainerWrapper($element, $info): ?ContainerElementInterface
            {
                return new PriorityCollectionElement($element, 0);
            }

            public function getOrderedElements(): array
            {
                // TODO: Implement getOrderedElements() method.
            }

            use StrictEqualObjectsTrait;
        };

        $this->assertEquals([1, 2, 3], $collection->toArray());
    }

    public function testOtherCollection() {
        $col1 = new PriorityCollection(13, [1, 2, 3]);
        $collection = new class($col1) extends AbstractContaineredCollection {
            use EqualObjectsTrait;

            protected function getContainerWrapper($element, $info): ?ContainerElementInterface
            {
                return new PriorityCollectionElement($element, 0);
            }

            public function getOrderedElements(): array
            {
                // TODO: Implement getOrderedElements() method.
            }
        };

        $this->assertEquals($col1->toArray(), $collection->toArray());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidConstructor() {
		$this->expectException(InvalidArgumentException::class);
        $collection = new class("Hello World!") extends AbstractContaineredCollection {
            use EqualObjectsTrait;

            protected function getContainerWrapper($element, $info): ?ContainerElementInterface
            {
            }

            public function getOrderedElements(): array
            {
            }
        };
    }

    public function testEnumerate()
    {
        $collection = new PriorityCollection(12, [1, 2, 3]);
        $collection->enumerate(function($value, $key, &$stop) use (&$array) {
            $array[$key] = $value;
            if(count($array) == 2)
                $stop = true;
        });

        $this->assertCount(2, $array);
        $this->assertEquals([1, 2], $array);
    }


    public function testContains()
    {
        $collection = new PriorityCollection(12, [1, 2, 3]);
        $this->assertTrue($collection->contains(3));
        $this->assertFalse($collection->contains(12));

        $this->assertFalse($collection->contains("3"));
    }

    public function testEnumerateReverse()
    {
        $collection = new PriorityCollection(12, [1, 2, 3]);
        $collection->enumerateReverse(function($value, $key, &$stop) use (&$array) {
            $array[$key] = $value;
            if(count($array) == 2)
                $stop = true;
        });

        $this->assertCount(2, $array);
        $this->assertEquals([3, 2], $array);
    }

    public function testPredefinedKeyedContainer() {
        $collection = new class extends AbstractContaineredCollection {
            use EqualObjectsTrait;

            protected function getContainerWrapper($element, $info): ?ContainerElementInterface
            {
                return new PriorityCollectionElement($element, 0);
            }

            public function getOrderedElements(): array
            {
            }

            public function addElement($element, $info = NULL): ContainerElementInterface
            {
                return parent::addElement($element, $info);
            }
        };

        $collection->addElement(56, [ $collection::INFO_INDEX_KEY => 'theKey' ]);
        $this->assertTrue($collection->contains(56));

        $this->assertEquals("theKey", $collection->indexOf(56));
    }

    public function testDuplicates() {
		$this->expectException(\TASoft\Collection\Exception\DuplicatedObjectException::class);

        $collection = new class extends AbstractContaineredCollection {
            use EqualObjectsTrait;

            protected function getContainerWrapper($element, $info): ?ContainerElementInterface
            {
                return new PriorityCollectionElement($element, 0);
            }

            public function getOrderedElements(): array
            {
            }

            public function addElement($element, $info = NULL): ContainerElementInterface
            {
                return parent::addElement($element, $info);
            }
        };

        $collection->addElement("Hello World");
        $collection->addElement("Test");
        $collection->addElement("Hello World");

        $this->assertEquals(["Hello World", "Test", "Hello World"], $collection->toArray());

        $collection->setAcceptsDuplicates(false);
        $collection->addElement("Test");
    }

    public function testUnwrappableElement() {
		$this->expectException(InvalidCollectionElementException::class);
        $collection = new class extends AbstractContaineredCollection {
            use EqualObjectsTrait;

            protected function getContainerWrapper($element, $info): ?ContainerElementInterface
            {
                return NULL;
            }

            public function getOrderedElements(): array
            {
            }

            public function addElement($element, $info = NULL): ContainerElementInterface
            {
                return parent::addElement($element, $info);
            }
        };

        $collection->addElement(52);
    }
}
