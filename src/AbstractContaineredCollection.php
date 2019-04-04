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


use TASoft\Collection\Element\ContainerElementInterface;
use TASoft\Collection\Exception\InvalidCollectionElementException;

/**
 * This class manages all collection elements into wrappers, implemented by ContainerElementInterface.
 * You may modify the collection instance property, but you must maintain all elements inside a wrapper!
 *
 * @package TASoft\Collection
 */
abstract class AbstractContaineredCollection extends AbstractCollection implements OrderedCollectionInterface
{
    const INFO_INDEX_KEY = 'index-key';

    private $acceptingDuplicates = true;

    /**
     * Because elements can be priorized, initial set is not available
     */
    public function __construct($collection = [], bool $acceptingDuplicates = true)
    {
        $this->acceptingDuplicates = $acceptingDuplicates;
        parent::__construct([]);

        if($collection instanceof AbstractContaineredCollection)
            $this->collection = $collection->collection;
        elseif(is_iterable($collection)) {
            foreach($collection as $idx => $value)
                $this->collection[$idx] = $value;
        } else {
            throw new \InvalidArgumentException("Constructor object for collection must be a collection or an iterable");
        }
    }

    public function &offsetGet($offset)
    {
        return $value = parent::offsetGet($offset) AND $value->getElement();
    }

    public function contains($object)
    {
        /** @var ContainerElementInterface $item */
        foreach($this->collection as $item) {
            if($this->objectsAreEqual($item->getElement() ,$object))
                return true;
        }
        return false;
    }

    public function enumerate(callable $callback)
    {
        parent::enumerate(function($object, $key , &$stop) use ($callback) {
            /** @var ContainerElementInterface $object */
            $callback($object->getElement(), $key, $stop);
        });
    }

    public function enumerateReverse(callable $callback)
    {
        parent::enumerateReverse(function($object, $key , &$stop) use ($callback) {
            /** @var ContainerElementInterface $object */
            $callback($object->getElement(), $key, $stop);
        });
    }

    public function indexOf($object)
    {
        /**
         * @var int $idx
         * @var ContainerElementInterface $o
         */
        foreach($this->collection as $idx => $o) {
            if($this->objectsAreEqual($o->getElement(), $object))
                return $idx;
        }
        return NULL;
    }

    public function indexesOf($object)
    {
        $indexes = [];
        /**
         * @var int $idx
         * @var ContainerElementInterface $o
         */
        foreach($this->collection as $idx => $o) {
            if($this->objectsAreEqual($o->getElement(), $object))
                $indexes[] = $idx;
        }
        return $indexes;
    }

    /**
     * This method is called for every new element that is added to a containered collection.
     * If it returns NULL, an exception is thrown
     *
     * @param $element
     * @param mixed $info Anything you passed as info in addElement method.
     *
     * @return ContainerElementInterface|null
     */
    abstract protected function getContainerWrapper($element, $info): ?ContainerElementInterface;

    /**
     * Add an element to the collection
     * This method is protected because you should declare your own add and remove methods.
     *
     * @param $element
     * @param null $info
     * @throws InvalidCollectionElementException
     */
    protected function addElement($element, $info = NULL) {
        $key = $info[ static::INFO_INDEX_KEY ] ?? NULL;
        unset($info[static::INFO_INDEX_KEY]);

        $wrapper = $this->getContainerWrapper($element, $info);
        if($wrapper instanceof ContainerElementInterface) {
            if(NULL !== $key)
                $this->collection[$key] = $wrapper;
            else
                $this->collection[] = $wrapper;
        } else {
            $e = new InvalidCollectionElementException("Element could not be wrapped");
            $e->setElement($element);
            throw $e;
        }
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