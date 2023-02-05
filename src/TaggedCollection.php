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
use TASoft\Collection\Element\TaggedCollectionElement;

/**
 * Tagged collections allow to assign tags to elements. You can search for elements by using tags.
 *
 * @package TASoft\Collection
 */
class TaggedCollection extends AbstractContaineredCollection
{
    use StrictEqualObjectsTrait;
    private $caseSensitive = true;

    private $_tags = [];
    private $_refs = [];

    /**
     * TaggedCollection constructor.
     * @param array $collection
     * @param bool $acceptingDuplicates
     * @param bool $caseSensitive
     * @param mixed ...$tags
     */
    public function __construct($collection = [], bool $acceptingDuplicates = true, bool $caseSensitive = true, ...$tags)
    {
        parent::__construct([], $acceptingDuplicates);
        $this->caseSensitive = $caseSensitive;
        if($collection && $tags) {
            foreach($collection as $col)
                $this->add($col, ...$tags);
        }
    }

    /**
     * @return bool
     */
    public function isCaseSensitive(): bool
    {
        return $this->caseSensitive;
    }

    /**
     * @inheritDoc
     */
    protected function getContainerWrapper($element, $info): ?ContainerElementInterface
    {
        return new TaggedCollectionElement($element, array_shift($info));
    }

    /**
     * Add an element to the collection under tags, separated as arguments
     *
     * @param $element
     * @param string ...$tags
     */
    public function add($element, ...$tags)
    {
        if($tags) {
            static $reference = 1;
            /** @var TaggedCollectionElement $container */
            $key = $reference++;
            $this->addElement($element, [ self::INFO_INDEX_KEY => $key, $key ]);

            foreach($tags as &$tag) {
                $tag = $this->isCaseSensitive() ? $tag : strtolower($tag);

                if(!in_array($key, $this->_tags[$tag] ?? []))
                    $this->_tags[$tag][] = $key;
            }
            $this->_refs[$key] = $tags;
        }
    }

    /**
     * Yields elements under passed tag
     *
     * @param $tag
     * @return \Generator
     */
    public function yieldElements($tag) {
        if($references = $this->_tags[$this->isCaseSensitive() ? $tag : strtolower($tag)] ?? NULL) {
            foreach($references as $reference) {
                yield $this->collection[$reference]->getElement();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset): array|null
    {
        $list = [];
        foreach($this->yieldElements($offset) as $element)
            $list[] = $element;

        return $list ?: NULL;
    }

    /**
     * Removes passed element and related tags
     * @param $element
     */
    public function remove($element) {
        $this->collection = array_filter($this->collection, function(TaggedCollectionElement $wrapper) use ($element) {
            if($this->objectsAreEqual($wrapper->getElement(), $element)) {
                $tags = $this->_refs[ $wrapper->getReference() ];
                foreach($tags as $tag) {
                    $this->_tags[$tag] = array_filter($this->_tags[$tag], function($v) use ($wrapper) {
                        return $v != $wrapper->getReference();
                    });
                }
                unset($this->_refs[ $wrapper->getReference() ]);
                return false;
            }
            return true;
        });
    }

    /**
     * Removes passed tags and elements, if they don't have any tag
     * @param string ...$tags
     */
    public function removeTags(...$tags) {
        foreach($tags as $tag) {
            $tag = $this->isCaseSensitive() ? $tag : strtolower($tag);

            if($references = $this->_tags[$tag] ?? NULL) {
                foreach($references as $reference) {
                    $this->_refs[$reference] = array_filter($this->_refs[$reference], function($tg) use ($tag) {
                        return $tag == $tg;
                    });
                    if(count($this->_refs[$reference]) < 1) {
                        unset($this->_refs[$reference]);
                        unset($this->collection[$reference]);
                    }
                }

                unset($this->_tags[$tag]);
            }
        }
    }

    /**
     * Removes all elements from collection
     */
    public function clear() {
        $this->collection = [];
        $this->_refs = [];
        $this->_tags = [];
    }
}