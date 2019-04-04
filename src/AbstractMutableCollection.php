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

namespace TASoft\Collection;


use TASoft\Collection\Exception\DuplicatedObjectException;
use TASoft\Collection\SortDescriptor\SortDescriptorInterface;

abstract class AbstractMutableCollection extends AbstractCollection
{
    private $acceptingDuplicates = true;

    public function __construct($collection = [], bool $acceptingDuplicates = true)
    {
        parent::__construct($collection);
        $this->acceptingDuplicates = $acceptingDuplicates;
    }

    public function offsetSet($offset, $value)
    {
        if(is_null($offset))
            $this->collection[] = $value;
        else
            $this->collection[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->collection[$offset]);
    }

    public function add(...$objects) {
        foreach($objects as $object) {
            if($this->acceptsDuplicates() || !$this->contains($object))
                $this[] = $object;
            else {
                $e = new DuplicatedObjectException("Duplicated object");
                $e->setObject($object);
                throw $e;
            }
        }
    }

    public function remove($object) {
        $this->collection = array_filter($this->collection, function($v) use ($object) {
            if($this->objectsAreEqual($v, $object))
                return true;
            return false;
        });
    }

    public function sort(array $sortDescriptors, bool $sortByIndexes = false) {
        $this::sortCollection( $this->collection, $sortDescriptors, $sortByIndexes );
    }

    public function filter(callable $filter = NULL) {
        $this->collection = $this::filterCollection($this->collection, $filter);
    }

    public static function filterCollection(iterable $collection, callable $filterCallback = NULL, int $depth = 0) {
        $list = [];
        if(!is_callable($filterCallback))
            $filterCallback = function($value, $d) { return $value ? true : false; };

        foreach($collection as $key => $value) {
            if(is_iterable($value)) {
                $value = static::filterCollection($value, $filterCallback, $depth+1);
            }
            if($filterCallback($value, $depth))
                $list[$key] = $value;
        }

        return $list;
    }

    public static function sortCollection(array $collection, array $sortDescriptors, bool $sortByIndexes = false) {
        $sorter = function($a, $b) use ($sortDescriptors) {
            foreach($sortDescriptors as $desc) {
                /** @var SortDescriptorInterface $desc */
                $result = $desc->compare($a, $b);
                $result *= $desc->isAscending() ? 1 : -1;

                if($result != self::ORDERED_SAME)
                    return $result;
            }
            return self::ORDERED_SAME;
        };

        if($sortByIndexes)
            uksort($collection, $sorter);
        else
            usort($collection, $sorter);
    }

    /**
     * @return bool
     */
    public function acceptsDuplicates(): bool
    {
        return $this->acceptingDuplicates;
    }

    /**
     * @param bool $acceptingDuplicates
     */
    public function setAcceptsDuplicates(bool $acceptingDuplicates)
    {
        $this->acceptingDuplicates = $acceptingDuplicates;
    }
}