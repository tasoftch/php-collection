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

namespace TASoft\Collection\SortDescriptor;

use TASoft\Collection\Element\CompareInterface;

/**
 * The default sort descriptor just compare using PHP built in <=> operator.
 * If a value implements CompareInterface, this instance is asked to compare against other value.
 *
 * @package TASoft\Collection
 */
class DefaultSortDescriptor implements SortDescriptorInterface
{
    private $ascending = true;

    /**
     * DefaultSortDescriptor constructor.
     * @param bool $ascending
     */
    public function __construct(bool $ascending = true)
    {
        $this->ascending = $ascending;
    }

    /**
     * @return bool
     */
    public function isAscending(): bool
    {
        return $this->ascending;
    }

    /**
     * If $A or $B under compare do not implement CompareInterface, this method is called to obtain the comparison value.
     * This is useful comparing data structures, to define which properties should be compared.
     *
     * @param $AorB
     * @return mixed
     */
    protected function getComparisonValue($AorB) {
        return $AorB;
    }

    /**
     * If A implements CompareInterface, it's up to it to compare against $B.
     * Otherwise the built in <=> operator will compare $A against $B
     *
     * @param $A
     * @param $B
     * @return int
     */
    public function compare($A, $B): int {
        if($A instanceof CompareInterface)
            return $A->compare($B);

        return $this->getComparisonValue($A) <=> $this->getComparisonValue($B);
    }
}