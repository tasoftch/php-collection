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
 * DefaultEqualTraitTest.php
 * php-collection
 *
 * Created on 2019-04-04 21:55 by thomas
 */

use PHPUnit\Framework\TestCase;
use TASoft\Collection\AbstractCollection;
use TASoft\Collection\CollectionInterface;
use TASoft\Collection\DefaultCollection;
use TASoft\Collection\Element\IsEqualClassInterface;
use TASoft\Collection\Element\IsEqualInterface;
use TASoft\Collection\Element\IsEqualStringInterface;
use TASoft\Collection\EqualObjectsTrait;

class DefaultEqualTraitTest extends TestCase
{
    /**
     * @param AbstractCollection $collection
     * @dataProvider collectionProvider
     */
    public function testEqualObjectAgainstString(AbstractCollection $collection) {
        $object = new class implements IsEqualStringInterface {
            public function isEqualToString(string $string): bool
            {
                return $string == 'Test';
            }
        };

        $collection[] = $object;

        $this->assertFalse($collection->contains(23));
        $this->assertTrue($collection->contains("Test"));
    }

    /**
     * @param AbstractCollection $collection
     * @dataProvider collectionProvider
     */
    public function testEqualClass(AbstractCollection $collection) {
        $collection[] = new MockEqualObjects("Haha");

        $this->assertTrue($collection->contains(new MockEqualObjects("Haha")));
        $this->assertFalse($collection->contains(new MockEqualObjects("Bala")));
    }

    /**
     * @param AbstractCollection $collection
     * @dataProvider collectionProvider
     */
    public function testIsEqual(AbstractCollection $collection) {
        $object = new class implements IsEqualInterface {
            public function isEqual($value): bool
            {
                return $value->test == 23 && $value->mach = true;
            }
        };
        $collection[] = $object;

        $obj = new stdClass();
        $obj->thomas = "tasoft";
        $obj->mach = false;
        $obj->test = 88;

        $this->assertFalse($collection->contains($obj));

        $obj->mach = true;
        $obj->test = 23;

        $this->assertTrue($collection->contains($obj));
    }

    public function collectionProvider() {
        return [
            [new DefaultCollection()],
            [new class extends DefaultCollection {
                use EqualObjectsTrait;
            }]
        ];
    }
}

class MockEqualObjects implements IsEqualClassInterface {
    public $string = "Test";

    /**
     * MockEqualObjects constructor.
     * @param string $string
     */
    public function __construct(string $string)
    {
        $this->string = $string;
    }

    /**
     * @param static $object
     * @return bool
     */
    public function isEqualTo($object): bool
    {
        return $object->string == $this->string;
    }
}

