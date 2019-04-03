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

/**
 * The case sensitive sort descriptor can distinguish between comparing case sensitive and case insensitive.
 *
 * @package TASoft\Collection
 */
class CaseSensitiveSortDescriptor extends DefaultSortDescriptor
{
    private $caseSensitive;

    /**
     * CaseSensitiveSortDescriptor constructor.
     * @param bool $caseSensitive
     * @param bool $ascending
     */
    public function __construct(bool $caseSensitive = true, bool $ascending = true)
    {
        parent::__construct($ascending);
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * @return bool
     */
    public function isCaseSensitive(): bool
    {
        return $this->caseSensitive;
    }

    /**
     * @inheritdoc
     */
    public function compare($A, $B): int
    {
        if($A instanceof CompareInterface)
            return $A->compare($B);

        return $this->isCaseSensitive() ?
            strcmp($this->getComparisonValue($A), $this->getComparisonValue($B)) :
            strcasecmp($this->getComparisonValue($A), $this->getComparisonValue($B));
    }
}