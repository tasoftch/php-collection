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
 * CollectionTest.php
 * php-collection
 *
 * Created on 03.04.19 17:54 by thomas
 */

use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testFindKey() {
        $collection = [
            'Tom', 'Jeff', 'John', 'Dale'
        ];
        $this->assertEquals(2, Collection::findKeyOf($collection, 'John'));
    }

    public function testMultipleFindKey() {
        $collection = [
            'Tom', 'Jeff', 'John', 'Dale', 'Tom', 'John'
        ];
        $this->assertEquals(2, Collection::findKeyOf($collection, 'John'));
    }

    public function testFindKeys() {
        $collection = [
            'Tom', 'Jeff', 'John', 'Dale', 'Tom', 'John'
        ];
        $this->assertEquals([2,5], Collection::findKeysOf($collection, 'John'));
    }

    public function testFindKeys2() {
        $collection = [
            'Tom', 'Jeff', 'John', 'Dale', 'Tom', 'John'
        ];
        $this->assertEquals([], Collection::findKeysOf($collection, 'JOHN'));
    }

    public function testFindKeysWithObjects() {
        $collection = [
            new MockUser('Tom'),
            new MockUser('Jeff'),
            new MockUser('John'),
            new MockUser('Dale'),
            new MockUser('Tom'),
            new MockUser('John')
        ];

        $tester = new Tester();
        $tester->test = 'JOHn';

        $this->assertEquals([2,5], Collection::findKeysOf($collection, $tester));
    }

    public function testSorting() {
        $collection = [
            new MockUser('Tom'),
            new MockUser('Jeff'),
            new MockUser('John'),
            new MockUser('Dale'),
            new MockUser('Thomas'),
            new MockUser('Johnny'),
            new MockUser('Tom', 5),
            new MockUser('Jeff', 5),
            new MockUser('John', 5),
            new MockUser('Dale', 5),
            new MockUser('Thomas', 5),
            new MockUser('Meier', 5),
            new MockUser('Mayer', 8),
            new MockUser('Maier', 2),
            new MockUser('Meyer', 7),
            new MockUser('Meier', 1)
        ];

        $descs = [
            new \TASoft\Core\Collection\SortDescriptor\GlobSortDescriptor([
                'Thomas',
                'M??er'
            ], function ($value) { return $value->name; })
        ];

        Collection::sortCollection($collection, $descs);
        $this->assertCount(16, $collection);
    }
}


class MockUser implements \TASoft\Core\Collection\EqualInterface {
    public $name;
    public $age;

    public function __construct($name, $age = 0)
    {
        $this->name = $name;
        $this->age = $age;
    }

    public function isEqual($object): bool {
        $object = (string) $object;
        return strcasecmp($object, $this->name) === 0;
    }
}

class Tester {
    public $test;
    public function __toString()
    {
        return $this->test;
    }
}
