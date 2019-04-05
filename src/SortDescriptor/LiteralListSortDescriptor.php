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
 * The literal list sort operator compares the values against a list or literal strings.
 * The first literal in list will be compared as the first in comparison
 *
 * @package TASoft\Collection
 */
class LiteralListSortDescriptor extends CaseSensitiveSortDescriptor
{
    /** @var array */
    private $list;

    public function __construct(array $list, bool $caseSensitive = true, bool $ascending = true)
    {
        parent::__construct($caseSensitive, $ascending);
        $this->list = $list;
    }

    /**
     * This method is called for every list entry until a listValue matches AorB.
     * If so, the index of that list value is used in comparison.
     *
     * @param $listValue
     * @param $AorB
     * @return bool
     */
    protected function inList($listValue, $AorB): bool {
        return $this->isCaseSensitive() ? strcmp($listValue, $AorB) == 0 : strcasecmp($listValue, $AorB) == 0;
    }

    /**
     * This method should return the index in list where $AorB matches or something, that orders $AorB out of range.
     * LiteralListSortDescriptor assumes integers as indexes, but you may specify whatever you want.
     *
     * Those indexes are compared now using <=> operator! (Override compare to change this if needed)
     *
     * @param $AorB
     * @return int|string|mixed
     */
    protected function getIndex($AorB) {
        foreach($this->list as $idx => $item) {
            if($this->inList($item, $AorB))
                return $idx;
        }
        return count($this->list);
    }

    /**
     * @inheritdoc
     */
    public function compare($A, $B): int
    {
        return
            $this->getIndex(
                $this->getComparisonValue($A)
            )
            <=>
            $this->getIndex(
                $this->getComparisonValue($B)
            );
    }
}