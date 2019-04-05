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
 * SortDescriptorsTest.php
 * php-collection
 *
 * Created on 2019-04-05 23:39 by thomas
 */

use PHPUnit\Framework\TestCase;
use TASoft\Collection\DefaultCollection;
use TASoft\Collection\Element\CompareInterface;
use TASoft\Collection\SortDescriptor\CallbackSortDescriptor;
use TASoft\Collection\SortDescriptor\CaseSensitiveSortDescriptor;
use TASoft\Collection\SortDescriptor\DefaultSortDescriptor;
use TASoft\Collection\SortDescriptor\GLOBListSortDescriptor;
use TASoft\Collection\SortDescriptor\LiteralListSortDescriptor;
use TASoft\Collection\SortDescriptor\RegexListSortDescriptor;

class SortDescriptorsTest extends TestCase
{
    /**
     * @dataProvider sortDescriptorProvider
     */
    public function testDefaultSortDescriptor($sortDescriptor) {
        $collection = new DefaultCollection([2, 3, 1]);
        $collection->sort( [$sortDescriptor] );


        $this->assertEquals([1, 2, 3], $collection->toArray());

        $collection[] = $obj1 = new class implements CompareInterface {
            public function compare($otherValue): int
            {
                return -1;
            }
        };

        // Index 0 is now 1!
        $collection[0] = $obj2 = new class implements CompareInterface {
            public function compare($otherValue): int
            {
                return 1;
            }
        };

        $collection->sort( [$sortDescriptor] );


        $this->assertEquals([$obj1, 2, 3, $obj2], $collection->toArray());
    }

    public function sortDescriptorProvider() {
        return [
            [new DefaultSortDescriptor()],
            [new CaseSensitiveSortDescriptor(true)]
        ];
    }

    public function testCaseSensitiveSortDescritporConstructio() {
        $des = new CaseSensitiveSortDescriptor(true, false);
        $this->assertTrue($des->isCaseSensitive());
        $this->assertFalse($des->isAscending());

        $this->assertEquals(-1, $des->compare("C", "c"));
    }

    public function testCallbackSortDescriptor() {
        $collection = new DefaultCollection([2, 3, 1]);
        $collection->sort( [new CallbackSortDescriptor(function ($A, $B) {
            return $A <=> $B;
        })] );

        $this->assertEquals([1, 2, 3], $collection->toArray());
    }

    public function testLiteralList() {
        $collection = new DefaultCollection([1, 2, 3, 4, 5, 6, 7, 8]);
        $sd = new LiteralListSortDescriptor([4, 6, 1, 2, 12, 15]);

        $collection->sort([ $sd ]);

        $this->assertEquals([4, 6, 1, 2, 3, 5, 7, 8], $collection->toArray());
    }

    public function testGLOBList() {
        $collection = new DefaultCollection([
            "file.php",
            "test.html",
            "other.php",
            "special.css",
            "logo.jpg",
            "inc.php",
            "styles.css",
            "link.html",
            "script.js",
            "license.txt",
            "image.jpg"
        ]);

        $collection->sort([new GLOBListSortDescriptor([
            "*.php",
            "*.unexisting",
            "*.css",
            "*.html"
        ]) ]);

        $this->assertEquals([
            "file.php",
            "other.php",
            "inc.php",
            "special.css",
            "styles.css",
            "test.html",
            "link.html",
            "logo.jpg",
            "script.js",
            "license.txt",
            "image.jpg"
        ], $collection->toArray());
    }

    public function testRegexList() {
        $collection = new DefaultCollection([
            "file.php",
            "test.html",
            "other.php",
            "special.css",
            "logo.jpg",
            "inc.php",
            "styles.css",
            "link.htm",
            "script.js",
            "license.txt",
            "image.jpg"
        ]);

        $collection->sort([new RegexListSortDescriptor([
            "/\.php$/i",
            "/inexistent|doesnotexist/i",
            "/\.css$/i",
            "/\.html?$/i"
        ]) ]);

        $this->assertEquals([
            "file.php",
            "other.php",
            "inc.php",
            "special.css",
            "styles.css",
            "test.html",
            "link.htm",
            "logo.jpg",
            "script.js",
            "license.txt",
            "image.jpg"
        ], $collection->toArray());
    }
}
