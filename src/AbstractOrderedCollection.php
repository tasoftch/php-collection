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

/**
 * Use the ordered collection to maintain a structure defined by wrapped elements
 * @package TASoft\Collection
 */
abstract class AbstractOrderedCollection extends AbstractContaineredCollection implements OrderedCollectionInterface
{
    private $_orderedCollection;

    /**
     * Call this method every time the collection changes to reorder collection on next use
     */

    protected function noteCollectionChanged() {
        $this->_orderedCollection = NULL;
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function getOrderedElements(): array
    {
        if($this->_orderedCollection === NULL) {
            $this->_orderedCollection = array_map(
                function (ContainerElementInterface $wrapper) {
                    return $wrapper->getElement();
                },
                $this->orderCollection($this->collection)
            );
        }
        return $this->_orderedCollection;
    }

    /**
     * Creates an iterator that iterates over ordered contents
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getOrderedElements());
    }

    /**
     * @inheritDoc
     */
    protected function addElement($element, $info = NULL): ContainerElementInterface
    {
        $el = parent::addElement($element, $info);
        $this->noteCollectionChanged();
        return $el;
    }


    /**
     * This method is called to order the collection
     * It will receive the wrappers and MUST return the wrappers. They are resolved after ordering.
     *
     * @param ContainerElementInterface[]
     * @return array
     */
    abstract protected function orderCollection(array $wrappers): array;
}