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

use TASoft\Collection\Exception\ImmutableCollectionException;

/**
 * Class AbstractCollection implements a standard collection
 * @package TASoft\Collection
 */
abstract class AbstractCollection implements CollectionInterface, \IteratorAggregate, \Countable
{
    const ORDERED_DESCENDING = -1;
    const ORDERED_SAME = 0;
    const ORDERED_ASCENDING = 1;

    const FILTER_DENY = -1;
    const FILTER_ABSTAIN = 0;
    const FILTER_ACCEPT = 1;

    protected $collection;

    public function __construct($collection = [])
    {
        if($collection instanceof AbstractCollection)
            $this->collection = $collection->collection;
        elseif(is_iterable($collection)) {
            foreach($collection as $idx => $value)
                $this->collection[$idx] = $value;
        } else {
            throw new \InvalidArgumentException("Constructor object for collection must be a collection or an iterable");
        }
    }

    public function count()
    {
        return count($this->collection);
    }


    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }


    public function offsetExists($offset)
    {
        return isset($this->collection[$offset]);
    }

    public function &offsetGet($offset)
    {
        return $this->collection[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $e = new ImmutableCollectionException("Collection is immutable");
        $e->setCollection($this);
        throw $e;
    }

    public function offsetUnset($offset)
    {
        $e = new ImmutableCollectionException("Collection is immutable");
        $e->setCollection($this);
        throw $e;
    }

    public function contains($object) {
        foreach($this->collection as $item) {
            if($this->objectsAreEqual($item ,$object))
                return true;
        }
        return false;
    }

    public function enumerate(callable $callback) {
        $stop = false;
        foreach($this->collection as $idx => $object) {
            $callback($object, $idx, $stop);
            if($stop) break;
        }
    }

    public function enumerateReverse(callable $callback) {
        $stop = false;
        foreach(array_reverse($this->collection) as $idx => $object) {
            $callback($object, $idx, $stop);
            if($stop) break;
        }
    }

    public function indexOf($object) {
        foreach($this->collection as $idx => $o) {
            if($this->objectsAreEqual($o, $object))
                return $idx;
        }
        return NULL;
    }

    public function indexesOf($object) {
        $indexes = [];
        foreach($this->collection as $idx => $o) {
            if($this->objectsAreEqual($o, $object))
                $indexes[] = $idx;
        }
        return $indexes;
    }

    abstract public function objectsAreEqual($object1, $object2): bool;

    public static function toArray($anything) {
        if(is_array($anything))
            return $anything;

        if(is_object($anything) && method_exists($anything, 'toArray'))
            return $anything->toArray();

        if(is_iterable($anything)) {
            $array = [];
            foreach($anything as $key => $value) {
                $array[$key] = $value;
            }
            return $array;
        }

        return (array) $anything;
    }

    public static function toCollection($anything): ?CollectionInterface {
        if($anything instanceof CollectionInterface)
            return $anything;
        if(is_object($anything) && method_exists($anything, 'toCollection'))
            return $anything->toCollection();
        if(is_iterable($anything)) {

        }
        return NULL;
    }
}