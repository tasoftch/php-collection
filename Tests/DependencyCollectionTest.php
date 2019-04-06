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
 * DependencyCollectionTest.php
 * php-collection
 *
 * Created on 2019-04-06 01:21 by thomas
 */

use PHPUnit\Framework\TestCase;
use TASoft\Collection\DependencyCollection;

class DependencyCollectionTest extends TestCase
{
    public function testPlain() {
        $dc = new DependencyCollection();
        $dc->add('thomas', 1);
        $dc->add('Daniela', 2);

        $this->assertEquals(['thomas', 'Daniela'], array_keys($dc->getOrderedElements()));
        $this->assertEquals([1, 2], array_values($dc->getOrderedElements()));
    }

    public function testDependentChecker() {
        $dc = new DependencyCollection();
        $dc->add('thomas', 1, ["Daniela"]);
        $dc->add('Daniela', 2);

        $this->assertEquals(['Daniela', 'thomas'], array_keys($dc->getOrderedElements()));
    }

    public function testMultipleDependencyChecker() {
        $dc = new DependencyCollection();
        $dc->add('thomas', 1, ["Daniela"]);
        $dc->add('Daniela', 2);
        $dc->add("Priska", 3, ["Daniela"]);
        $dc->add("Bettina", 6, ["thomas"]);

        $this->assertEquals(['Daniela', 'Priska', "thomas", 'Bettina'], array_keys($dc->getOrderedElements()));
    }

    /**
     * @expectedException TASoft\Collection\Exception\CircularDependencyException
     */
    public function testCircularDependencyChecker() {
        $dc = new DependencyCollection();
        $dc->add('thomas', 1, ["Daniela"]);
        $dc->add('Daniela', 2, ["Priska"]);
        $dc->add('Priska', 2, ["Bettina"]);
        $dc->add('Bettina', 2, ["thomas"]); // circular dependency.


        print_r( array_keys($dc->getOrderedElements()) );
    }

    /**
     * @expectedException TASoft\Collection\Exception\CircularDependencyException
     */
    public function testRecursiveDependency() {
        $dc = new DependencyCollection();
        $dc->add('thomas', 1, ["Daniela"]);
        $dc->add('Daniela', 2, ["Priska"]);
        $dc->add('Priska', 2, ["Bettina"]);
        $dc->add('Bettina', 2, ["Daniela"]); // also circular dependency but entry point stays

        print_r( array_keys($dc->getOrderedElements()) );
    }

    public function testRecursiveDependency2() {
        $dc = new DependencyCollection(true);
        $dc->add('thomas', 1, ["Daniela"]);
        $dc->add('Daniela', 2, ["Priska"]);
        $dc->add('Priska', 2, ["Bettina"]);
        $dc->add('Bettina', 2, ["Daniela"]); // also circular dependency but entry point stays

        $this->assertEquals(['Bettina', 'Priska', 'Daniela', 'thomas'], array_keys($dc->getOrderedElements()));
    }

    public function testComplexPackages() {
        $dc = new DependencyCollection();
        $dc->add('thomas', 1, ["Daniela", 'Priska']);
        $dc->add('Daniela', 2);
        $dc->add("Priska", 3, ["Daniela"]);
        $dc->add("Bettina", 6, ["thomas"]);

        $this->assertEquals(["Daniela", "Priska"], $dc->getElementDependencies("thomas"));
        $this->assertEquals(['Daniela', 'Priska', 'thomas', 'Bettina'], array_keys($dc->getOrderedElements()));
    }

    /**
     * @expectedException PHPUnit\Framework\Error\Warning
     */
    public function testUnexistingDependency() {
        $dc = new DependencyCollection();
        $dc->add('thomas', 1, ["Daniela", 'Priska']);
        $dc->add('Daniela', 2);
        $dc->add("Priska", 3, ["Daniela", "unexistent"]);

        $this->assertEquals(['Daniela', 'Priska', 'thomas', 'Bettina'], array_keys($dc->getOrderedElements()));
    }

    public function testRemoveElementName() {
        $dc = new DependencyCollection();
        $dc->add('thomas', 1, ["Daniela", 'Priska']);
        $dc->add('Daniela', 2);
        $dc->add("Priska", 3, ["Daniela"]);
        $dc->add("Bettina", 6, ["thomas"]);

        $dc->remove("Priska");
        $this->assertEquals(["Daniela", "thomas", "Bettina"], array_keys($dc->getOrderedElements()));
    }

    public function testRemoveElementNameWithDependencies() {
        $dc = new DependencyCollection();
        $dc->add('thomas', 1, ["Daniela", 'Priska']);
        $dc->add('Daniela', 2);
        $dc->add("Priska", 3, ["Daniela"]);
        $dc->add("Bettina", 6, ["thomas"]);

        $dc->remove("thomas", true);
        $this->assertEquals(["Bettina"], array_keys($dc->getOrderedElements()));
    }

    public function testRemoveElement() {
        $dc = new DependencyCollection();
        $dc->add('thomas', 1, ["Daniela", 'Priska']);
        $dc->add('Daniela', 2);
        $dc->add("Priska", 3, ["Daniela"]);
        $dc->add("Bettina", 6, ["thomas"]);

        $dc->removeElement(3);
        $this->assertEquals(["Daniela", "thomas", "Bettina"], array_keys($dc->getOrderedElements()));
    }
}
