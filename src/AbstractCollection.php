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

use ReturnTypeWillChange;
use TASoft\Collection\Exception\ImmutableCollectionException;

/**
 * Class AbstractCollection implements a standard collection
 * @package TASoft\Collection
 */
abstract class AbstractCollection implements CollectionInterface, \IteratorAggregate, \Countable
{
    protected $collection = [];

    /**
     * Pass any iterable into a collection to initialize it.
     * @param array $collection
     */
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

    /**
     * Make collection countable
     * @return int
     */
    public function count(): int
	{
        return count($this->collection);
    }

    /**
     * Make collection iterable
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator(): \Traversable
	{
        return new \ArrayIterator($this->collection);
    }

    /**
     * Make collection array accessable
     *
     * @param mixed $offset
     * @return bool
     */
    #[ReturnTypeWillChange] public function offsetExists($offset)
    {
        return isset($this->collection[$offset]);
    }

    /**
     * Make collection array accessable
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
	{
        return $this->collection[$offset] ?? NULL;
    }

    /**
     * Make collection array accessable
     *
     * @param mixed $offset
     * @param mixed $value
     */
    #[ReturnTypeWillChange] public function offsetSet($offset, $value)
    {
        $e = new ImmutableCollectionException("Collection is immutable");
        $e->setCollection($this);
        throw $e;
    }

    /**
     * Make collection array accessable
     *
     * @param mixed $offset
     */
    #[ReturnTypeWillChange] public function offsetUnset($offset)
    {
        $e = new ImmutableCollectionException("Collection is immutable");
        $e->setCollection($this);
        throw $e;
    }

    /**
     * Checks, if the collection contains an object
     *
     * @param $object
     * @return bool
     */
    public function contains($object)
	{
        foreach($this->collection as $item) {
            if($this->objectsAreEqual($item ,$object))
                return true;
        }
        return false;
    }

    /**
     * Enumerates all objects in collection.
     * Passes three arguments into callback: $object, $index, &$stop as boolean to break enumeration
     *
     * @param callable $callback
     * @see AbstractCollection::enumerateReverse()
     */
    public function enumerate(callable $callback) {
        $stop = false;
        foreach($this->collection as $idx => $object) {
            $callback($object, $idx, $stop);
            if($stop) break;
        }
    }

    /**
     * Enumerates all objects in collection in reversed order.
     * Passes three arguments into callback: $object, $index, &$stop as boolean to break enumeration
     *
     * @param callable $callback
     */
    public function enumerateReverse(callable $callback) {
        $stop = false;
        foreach(array_reverse($this->collection) as $idx => $object) {
            $callback($object, $idx, $stop);
            if($stop) break;
        }
    }

    /**
     * Searches index of an object
     *
     * @param $object
     * @return int|null|string
     */
    public function indexOf($object) {
        foreach($this->collection as $idx => $o) {
            if($this->objectsAreEqual($o, $object))
                return $idx;
        }
        return NULL;
    }

    /**
     * Searches all indexes of an object
     *
     * @param $object
     * @return array
     */
    public function indexesOf($object) {
        $indexes = [];
        foreach($this->collection as $idx => $o) {
            if($this->objectsAreEqual($o, $object))
                $indexes[] = $idx;
        }
        return $indexes;
    }

    /**
     * This method is called to decide if two objects are equal
     *
     * @param $object1
     * @param $object2
     * @return bool
     */
    abstract public function objectsAreEqual($object1, $object2): bool;

    /**
     * Returns true, if value is a value that can be used as collection
     *
     * @param $value
     * @return bool
     */
    public static function isCollection($value): bool {
        return is_iterable($value);
    }

    /**
     * Tries to convert anything into an array
     *
     * @param $anything
     * @return array
     */
    public static function makeArray($anything) {
        if(is_array($anything))
            return $anything;

        if(is_object($anything) && method_exists($anything, 'toArray') && !($anything instanceof self))
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

    public function toArray() {
        return static::makeArray($this);
    }

    /**
     * Tries to convert anything into a collection
     *
     * @param $anything
     * @return null|CollectionInterface
     */
    public static function makeCollection($anything): ?CollectionInterface {
        if($anything instanceof CollectionInterface)
            return $anything;
        if(is_object($anything) && method_exists($anything, 'toCollection'))
            return $anything->toCollection();
        if(is_iterable($anything)) {
            return new static($anything);
        }
        return NULL;
    }
}