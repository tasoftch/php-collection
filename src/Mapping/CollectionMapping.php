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

namespace TASoft\Collection\Mapping;

/**
 * The collection mapping iterates over a collection and let
 * @package TASoft\Collection\Mapping
 */
class CollectionMapping
{
    /** @var MapperInterface */
    private $mapper;

    /**
     * CollectionMapping constructor.
     * @param MapperInterface $mapper
     */
    public function __construct(MapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Mapping the collection into something different
     * @param iterable $collection
     * @return iterable
     */
    public function mapCollection(iterable $collection) {
        $mapper = $this->getMapper();
        $handler = function($key, $value, $depth = 0) use ($mapper, &$handler) {
            if($mapper instanceof RecursiveMapperInterface) {
                if($mapper->hasChildren($key, $value, $depth)) {
                    foreach($value as $k => &$vv)
                        $vv = $handler($k, $vv, $depth+1);
                    return $value;
                }
            }
            return $mapper->map($key, $value);
        };

        if(is_array($collection)) {
            foreach($collection as $key => &$value) {
                $value = $handler($key, $value);
            }
        } else {
            $changes = [];
            foreach($collection as $key => $value) {
                $val = $handler($key, $value);
                if($val !== $value)
                    $changes[$key] = $val;
            }
            if($changes) {
                foreach($changes as $key => $value)
                    $collection[$key] = $value;
            }
        }


        return $collection;
    }

    /**
     * @return MapperInterface
     */
    public function getMapper(): MapperInterface
    {
        return $this->mapper;
    }

    /**
     * @param MapperInterface $mapper
     */
    public function setMapper(MapperInterface $mapper): void
    {
        $this->mapper = $mapper;
    }
}