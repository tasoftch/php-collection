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
use TASoft\Collection\Element\DependencyCollectonElement;
use TASoft\Collection\Exception\CircularDependencyException;

abstract class AbstractDependencyCollection extends AbstractOrderedCollection
{
    public function __construct()
    {
        parent::__construct([], true);
    }

    /**
     * @inheritDoc
     */
    protected function getContainerWrapper($element, $info): ?ContainerElementInterface
    {
        return new DependencyCollectonElement($element, $info[1], $info[0]);
    }

    /**
     * Called if a dependency could not be found. Return an element to specify or null to ignore missing dependency.
     *
     * @param string $requiredName
     * @return DependencyCollectonElement|null
     */
    abstract protected function getUnexistingRequiredElement(string $requiredName): ?DependencyCollectonElement;

    /**
     * This method is called to resolve circular dependencies.
     *
     *
     *
     * @param DependencyCollectonElement $element               The element requiring dependency
     * @param DependencyCollectonElement $circularDependency    The required dependency that requires $element
     * @return DependencyCollectonElement|null                  The priorized dependency
     * @see AbstractDependencyCollection::getCircularEntryPoints
     */
    abstract protected function handleCircularDependency(DependencyCollectonElement $element, DependencyCollectonElement $circularDependency): ?DependencyCollectonElement;

    /**
     * This method is called to order or modify the root elements. Root elements are all elements, that are not required by another element.
     * If the $resolvedRootElements array is empty, the collection has a circular dependency chain. In this case you must specify at least one root element, otherwise the collection can not order.
     *
     * @example A => B, B => (C and D), D => A       // A nor B nor C nor D are not required, so no entry point resolvable
     *
     * @param array $resolvedRootElements            ordered list of required references
     * @return array                                 Return one or more root elements
     */
    protected function getRootElements(array $resolvedRootElements): array {
        return $resolvedRootElements;
    }

    /**
     * Order collection against dependencies
     *
     * @param array $wrappers
     * @return array
     */
    protected function orderCollection(array $wrappers): array
    {
        // First, reset all dependencies and required references
        array_walk($wrappers, function(DependencyCollectonElement $element) {
            $element->_realDependencies = [];
            $element->_realDepends = [];
        });

        // Then create the real tree (real dependency elements) of dependencies and required references

        $createdDependencies = [];

        $dependencyResolver = function(DependencyCollectonElement $element) use (&$dependencyResolver, &$createdDependencies) {
            foreach($element->getDependencies() as $depName) {
                if(!isset($element->_realDependencies[$depName])) {
                    if(!isset($this->collection[$depName])) {
                        // Dependency does not exist. create it if possible
                        $this->collection[$depName] = NULL;

                        /** @var DependencyCollectonElement $dep */
                        if($dep = $this->getUnexistingRequiredElement($depName)) {
                            if(!isset($dep->_realDependencies))
                                $dep->_realDependencies = [];
                            if(!isset($dep->_realDepends))
                                $dep->_realDepends = [];

                            $dependencyResolver($dep);
                            $this->collection[$depName] = $dep;
                            $createdDependencies[$depName] = $dep;
                        } else
                            continue;
                    } else
                        $dep = $this->collection[$depName];

                    $element->_realDependencies[$depName] = $dep;
                    $dep->_realDepends[$element->getName()] = $element;
                }
            }
        };

        array_walk($wrappers, $dependencyResolver);

        if($createdDependencies)
            $wrappers = array_merge($wrappers, $createdDependencies);

        $initial = [];
        if(count($wrappers)>1) {
            // Now order the wrappers that the ones with zero required references is first, additionally capture all which are not referenced as dependency
            uasort($wrappers, function(DependencyCollectonElement $A, DependencyCollectonElement $B) use (&$initial) {
                if(count($A->_realDepends) == 0 && !in_array($A, $initial))
                    $initial[] = $A;
                if(count($B->_realDepends) == 0 && !in_array($B, $initial))
                    $initial[] = $B;

                return count($A->_realDepends) <=> count($B->_realDepends);
            });
        } else {
            $initial = $wrappers;
        }


        $orderedWrappers = [];

        $resolveAlgorythm = function(DependencyCollectonElement $element, array $recursion = []) use (&$resolveAlgorythm, &$orderedWrappers) {
            /** @var DependencyCollectonElement $dependency */
            $circulars = [];

            foreach($element->_realDependencies as $dependency) {
                $depName = $dependency->getName();

                if(isset($recursion[$depName])) {
                    if($p = $this->handleCircularDependency($dependency, $element)) {
                        $circulars[] = $p;
                        continue;
                    } else {
                        $theStack = array_keys( $recursion );

                        $chain = implode(" >> ", $theStack);
                        if(!$chain) {
                            $chain = $element->getName();
                            $last = "";
                        } else {
                            $last = sprintf(" >> #%s! (recursion!)", $dependency->getName());
                        }
                        $e = new CircularDependencyException("Can not resolve dependencies for %s :: $chain$last", 0, NULL, $element->getName());
                        $e->setDependency($element);
                        $e->setDependencyChain($recursion);
                        throw $e;
                    }
                }

                $localRecursion = $recursion;
                $localRecursion[$depName] = $dependency;
                $resolveAlgorythm($dependency, $localRecursion);
            }

            $add = function(DependencyCollectonElement $dep) use (&$orderedWrappers) {
                if(!isset($orderedWrappers[$dep->getName()]))
                    $orderedWrappers[ $dep->getName() ] = $dep;
            };

            foreach ($circulars as $c)
                $add($c);

            $add($element);
        };

        if(count($initial = $this->getRootElements($initial)) < 1) {
            $e = new CircularDependencyException("Could not resolve dependencies to get an entry point");
            throw $e;
        }

        foreach($initial as $init)
            $resolveAlgorythm($init);

        return $orderedWrappers;
    }

    /**
     * Add new element with name and dependent names of other elements.
     *
     * @param string $name
     * @param $element
     * @param array $dependencies
     */
    public function add(string $name, $element, array $dependencies = []) {
        $this->addElement($element, [self::INFO_INDEX_KEY=>$name, $name, $dependencies]);
        $this->noteCollectionChanged();
    }

    /**
     * Removes element with passed name and its dependencies if required
     *
     * @param string $name
     * @param bool $removeDependencies      // Removes also dependent elements
     * @param bool $removeReferences         // Removes also all required references to element
     */
    public function remove(string $name, bool $removeDependencies = false, bool $removeReferences = true) {
        $remover = function($name) use (&$remover, $removeDependencies, $removeReferences) {
            $dependencies = $removeDependencies ? [] : NULL;

            $this->collection = array_filter($this->collection, function(DependencyCollectonElement $element) use ($name, &$dependencies) {
                if($element->getName() == $name) {
                    if(is_array($dependencies)) {
                        $dependencies = array_merge($dependencies, $element->getDependencies());
                    }

                    return false;
                }
                return true;
            });

            if($removeReferences) {
                array_walk($this->collection, function (DependencyCollectonElement $element) use ($name) {
                    $element->removeDependency($name);
                });
            }

            if($dependencies && $dependencies = array_unique($dependencies)) {
                foreach($dependencies as $dependency) {
                    if($dependency != $name)
                        $remover($dependency);
                }
            }
        };
        $remover($name);
        $this->noteCollectionChanged();
    }

    /**
     * Removes all elements from collection
     */
    public function clear() {
        $this->collection = [];
        $this->noteCollectionChanged();
    }

    /**
     * Removes all occurrences holding element
     *
     * @param $element
     * @param bool $removeDependencies
     */
    public function removeElement($element, bool $removeDependencies = false) {
        foreach(array_filter($this->collection, function(DependencyCollectonElement $e) use ($element) {
            return $this->objectsAreEqual($e->getElement(), $element);
        }) as $toRemove) {
            $this->remove($toRemove->getName(), $removeDependencies);
        }
    }

    /**
     * Returns the registered dependency names of an element or NULL if the element does not exist
     *
     * @param string $name
     * @return array|null
     */
    public function getElementDependencies(string $name): ?array {
        $element = $this->collection[$name] ?? NULL;
        if($element instanceof DependencyCollectonElement)
            return $element->getDependencies();
        return NULL;
    }

	/**
	 * Gets the real dependencies of an element
	 *
	 * @param string $name
	 * @return array|null
	 */
    public function getResolvedElementDependencies(string $name): ?array {
    	$this->getOrderedElements();
    	$element = $this->collection[$name] ?? NULL;
    	if($element instanceof DependencyCollectonElement) {
    		if(isset($element->_realDependencies)) {
    			return array_map(function(DependencyCollectonElement $V) {
    				return $V->getElement();
				}, $element->_realDependencies);
			}
		}
    	return NULL;
	}

	/**
	 * Gets all elements depending on the specified element
	 *
	 * @param string $name
	 * @return array|null
	 */
	public function getResolvedElementDepends(string $name): ?array {
		$this->getOrderedElements();
		$element = $this->collection[$name] ?? NULL;
		if($element instanceof DependencyCollectonElement) {
			if(isset($element->_realDepends)) {
				return array_map(function(DependencyCollectonElement $V) {
					return $V->getElement();
				}, $element->_realDepends);
			}
		}
		return NULL;
	}

	/**
	 * Gets an identifier containing recursive array tree of the dependencies.
	 *
	 *
	 * @param string $name
	 * @param int $depth
	 * @return array|null
	 */
	public function getDependencyBranches(string $name, int $depth = 10): ?array {
		$iterator = function($name, $deep) use ($depth, &$iterator) {
			if($deep < $depth) {
				$deps = $this->getResolvedElementDependencies($name);
				$dependencies = [];

				foreach (array_keys($deps) as $dK) {
					$dependencies[$dK] = $iterator($dK, $deep+1);
				}

				return $dependencies;
			}
			return false;
		};

		return $iterator($name, 1);
	}

	/**
	 * Gets all dependencies of the specified element.
	 *
	 * @param string $name
	 * @param int $depth
	 * @return array|null
	 */
	public function getRecursiveDependencies(string $name, int $depth = 10): ?array {
		$element = $this->collection[$name] ?? NULL;
		if($element instanceof DependencyCollectonElement) {
			$dependencies = [];
			$iterator = function($name, $deep) use ($depth, &$iterator, &$dependencies) {
				if($deep < $depth) {
					$deps = $this->getResolvedElementDependencies($name);
					foreach ($deps as $dK => $dep) {
						if(!isset($dependencies[$dK])) {
							$dependencies[$dK] = $dep;
							$iterator($dK, $deep+1);
						}
					}
				}
			};
			$iterator($name, 1);
			return $dependencies;
		}
		return NULL;
	}

	/**
	 * Gets all elements that depends on the specified element.
	 *
	 * @param string $name
	 * @param int $depth
	 * @return array|null
	 */
	public function getRecursiveDepends(string $name, int $depth = 10): ?array {
		$element = $this->collection[$name] ?? NULL;
		if($element instanceof DependencyCollectonElement) {
			$dependencies = [];
			$iterator = function($name, $deep) use ($depth, &$iterator, &$dependencies) {
				if($deep < $depth) {
					$deps = $this->getResolvedElementDepends($name);
					foreach ($deps as $dK => $dep) {
						if(!isset($dependencies[$dK])) {
							$dependencies[$dK] = $dep;
							$iterator($dK, $deep+1);
						}
					}
				}
			};
			$iterator($name, 1);
			return $dependencies;
		}
		return NULL;
	}
}