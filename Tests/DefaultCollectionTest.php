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
 * DefaultCollectionTest.php
 * php-collection
 *
 * Created on 2019-04-04 20:54 by thomas
 */

use PHPUnit\Framework\TestCase;
use TASoft\Collection\AbstractCollection;
use TASoft\Collection\CollectionInterface;
use TASoft\Collection\DefaultCollection;
use TASoft\Collection\Exception\ImmutableCollectionException;
use TASoft\Collection\ImmutableCollection;
use TASoft\Collection\SortDescriptor\DefaultSortDescriptor;

class DefaultCollectionTest extends TestCase
{
    public function testContructionWithIterable() {
        $collection = new DefaultCollection([1, 2, 3]);
        $this->assertCount(3, $collection);
        $this->assertInstanceOf(Iterator::class, $collection->getIterator());

        $this->assertEquals([1, 2, 3], $collection->toArray());
    }

    public function testConstructionWithCollection() {
        $col1 = new DefaultCollection([1, 2, 3]);

        $collection = new DefaultCollection($col1);
        $this->assertCount(3, $collection);
        $this->assertInstanceOf(Iterator::class, $collection->getIterator());

        $this->assertEquals([1, 2, 3], $collection->toArray());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidConstruction() {
        $collection = new DefaultCollection("Hello World!");
    }

    public function testMakeAnythingToArray() {
        $this->assertEquals([1, 2, 3], AbstractCollection::makeArray([1, 2, 3]));

        $obj = new stdClass();
        $obj->a = 1;
        $obj->b = 2;
        $obj->c = 3;

        $this->assertEquals(["a" => 1, "b" => 2, "c" => 3], AbstractCollection::makeArray($obj));
        $collection = new DefaultCollection([1, 2, 3]);

        $this->assertEquals([1, 2, 3], AbstractCollection::makeArray($collection));
        $toArrayObject = new class() {
            public function toArray() {
                return [1, 2, 3];
            }
        };

        $this->assertEquals([1, 2, 3], AbstractCollection::makeArray($toArrayObject));

        $iterator = new ArrayIterator([1, 2, 3]);
        $this->assertEquals([1, 2, 3], AbstractCollection::makeArray($iterator));

        $value = AbstractCollection::makeArray("Hello");
        $this->assertEquals(["Hello"], $value);
    }

    public function testMakeAnythingToCollection() {
        $this->assertNull(AbstractCollection::makeCollection("Hello World!"));

        $collection = new DefaultCollection([1, 2, 3]);

        $made = AbstractCollection::makeCollection($collection);
        $this->assertInstanceOf(CollectionInterface::class, $made);
        $this->assertCount(3, $made);
        $this->assertEquals($collection, $made);


        $toCollectionObject = new class() {
            public function toCollection() {
                return new DefaultCollection( [1, 2, 3] );
            }
        };

        $this->assertEquals($collection, AbstractCollection::makeCollection($toCollectionObject));

        $iterator = new ArrayIterator([1, 2, 3]);
        $this->assertEquals($collection, DefaultCollection::makeCollection($iterator));
    }

    /**
     * @expectedException  \Error
     */
    public function testMakeIterableToAbstractCollection() {
        $collection = NULL;

        $iterator = new ArrayIterator([1, 2, 3]);
        $this->assertEquals($collection, AbstractCollection::makeCollection($iterator));
    }

    public function testIsCollection() {
        $this->assertTrue( AbstractCollection::isCollection(new ArrayIterator([2, 3, 4])) );
        $this->assertFalse( AbstractCollection::isCollection(15) );
    }

    public function testDirectArrayAccess() {
        $collection = new DefaultCollection([1, 2, 3]);
        $this->assertEquals(2, $collection[1]);

        $this->assertArrayHasKey(2, $collection);
    }

    /**
     * @expectedException TASoft\Collection\Exception\ImmutableCollectionException
     */
    public function testImmutableKeyAccess() {
        $collection = new ImmutableCollection([1]);
        try {
            $collection[] = 4;
        } catch (ImmutableCollectionException $exception) {
            $this->assertSame($collection, $exception->getCollection());
        }

        $collection[] = 8;
    }

    /**
     * @expectedException TASoft\Collection\Exception\ImmutableCollectionException
     */
    public function testImmutableUnsetAccess() {
        $collection = new ImmutableCollection([1]);
        unset($collection[3]);
    }

    public function testIndexOf() {
        $collection = new DefaultCollection([1, 2, 3, 4, 5, 6]);

        $this->assertEquals(3, $collection->indexOf(4));
        $this->assertNull($collection->indexOf("Hello World!"));
    }

    public function testIndexesOf() {
        $collection = new DefaultCollection([1, 2, 3, 4, 5, 6, 3, 9, 2, 5, 4]);

        $this->assertEquals([3, 10], $collection->indexesOf(4));
    }

    public function testContains() {
        $collection = new DefaultCollection([1, 2, 3, 4, 5, 6]);

        $this->assertTrue($collection->contains(4));
        $this->assertFalse($collection->contains(12));
    }

    public function testEnumerator() {
        $collection = new DefaultCollection([1, 2, 3]);
        $collection->enumerate(function($value) use (&$array) {
            $array[] = $value;
        });
        $this->assertEquals([1, 2, 3], $array);
    }

    public function testReverseEnumerator() {
        $collection = new DefaultCollection([1, 2, 3]);
        $collection->enumerateReverse(function($value) use (&$array) {
            $array[] = $value;
        });
        $this->assertEquals([3, 2, 1], $array);
    }

    public function testAddObjects() {
        $collection = new DefaultCollection([1, 2, 3]);
        $collection[] = 5;
        $collection["he"] = 8;

        $this->assertEquals([1, 2, 3, 5, 'he' => 8], $collection->toArray());
    }

    public function testUnset() {
        $collection = new DefaultCollection([1, 2, 3]);
        unset($collection[0]);

        $this->assertEquals([2, 3], array_values($collection->toArray()));

        unset($collection[14]);
        $this->assertEquals([2, 3], array_values ($collection->toArray() ));
    }

    public function testAddUsingMethod() {
        $collection = new DefaultCollection([1, 2]);
        $collection->add(4, 6, 7);

        $this->assertEquals([1, 2, 4, 6, 7], $collection->toArray());
    }

    public function testAddDuplicates() {
        $collection = new DefaultCollection([1, 2, 4]);
        $collection->add(4, 6, 7);

        $this->assertEquals([1, 2, 4, 4, 6, 7], $collection->toArray());
    }

    /**
     * @expectedException TASoft\Collection\Exception\DuplicatedObjectException
     */
    public function testDeniedDuplicates() {
        $collection = new DefaultCollection([1, 2, 4]);
        $collection->setAcceptsDuplicates(false);

        $collection->add(4, 6, 7);

        $this->assertEquals([1, 2, 4, 4, 6, 7], $collection->toArray());
    }

    public function testRemoveObject() {
        $collection = new DefaultCollection([1, 2, 3, 4]);
        $collection->remove(3);
        $this->assertEquals([1, 2, 4], array_values($collection->toArray()));
    }

    public function testSimpleSorting() {
        $collection = new DefaultCollection([6, 3, 9, 1, 4, 3]);
        $collection->sort( [ new DefaultSortDescriptor(true) ] );

        $this->assertEquals([1, 3,3,4, 6, 9], $collection->toArray());
    }

    public function testIndexedSorting() {
        $collection = new DefaultCollection();
        $collection["x"] = 12;
        $collection["s"] = 7;
        $collection["b"] = 9;
        $collection["g"] = 1;

        $collection->sort([new DefaultSortDescriptor(false)], true);

        $this->assertEquals(["x" => 12, 's' => 7, 'g' => 1, 'b' => 9], $collection->toArray());
    }

    public function testFilter() {
        $collection = new DefaultCollection([6, 3, 9, 1, 4, 3]);
        $collection->filter(function($value) {
            return $value > 3;
        });
        $this->assertEquals([6, 9, 4], array_values($collection->toArray()));
    }

    public function testNullFilter() {
        $collection = new DefaultCollection([6, 0, 9, NULL, 4, ""]);
        $collection->filter();
        $this->assertEquals([6, 9, 4], array_values($collection->toArray()));
    }

    public function testRecursiveFilter() {
        $collection = new DefaultCollection([6, [1, 2, 3], 9, NULL, 4, ""]);
        $collection->filter(function($value, $depth) use (&$testDepth) {
            $testDepth = max($testDepth, $depth);
            return $value ? true : false;
        });

        $this->assertEquals(1, $testDepth);
    }
}
