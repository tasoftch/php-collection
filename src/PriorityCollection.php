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
use TASoft\Collection\Element\PriorityCollectionElement;

class PriorityCollection extends AbstractOrderedCollection
{
    use StrictEqualObjectsTrait;

    public function __construct(int $priority = NULL, $collection = [], bool $acceptingDuplicates = true)
    {
        parent::__construct([], $acceptingDuplicates);
        if(NULL !== $priority && $collection)
            $this->add($priority, ...$collection);
    }

    protected function getContainerWrapper($element, $info): ?ContainerElementInterface
    {
        return new PriorityCollectionElement($element, $info);
    }

    protected function orderCollection(array $wrappers): array
    {
        usort($wrappers, function(PriorityCollectionElement $A, PriorityCollectionElement $B) {
            $cmp = $A->getPriority() <=> $B->getPriority();
            if($cmp == 0)
                return $A->getInstanceCount() <=> $B->getInstanceCount();
            return $cmp;
        });
        return $wrappers;
    }

    /**
     * Adds all passed objects with priority
     *
     * @param int $priority
     * @param mixed ...$objects
     */
    public function add(int $priority, ...$objects) {
        foreach($objects as $object) {
            $this->addElement($object, $priority);
        }
    }

    /**
     * @param $element
     */
    public function remove($element) {
        $this->collection = array_filter($this->collection, function(PriorityCollectionElement $wrapper) use ($element) {
            return !$this->objectsAreEqual($wrapper->getElement(), $element);
        });
        $this->noteCollectionChanged();
    }

    /**
     * Removes all elements from collection
     */
    public function clear() {
        $this->collection = [];
        $this->noteCollectionChanged();
    }
}