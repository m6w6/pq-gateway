<?php

namespace pq\Mapper;

use pq\Mapper\Property\All;
use pq\Mapper\Property\Field;
use pq\Mapper\Property\Ref;
use ReflectionProperty;
use UnexpectedValueException;

class Mapper
{
	/**
	 * @var MapInterface[]
	 */
	private $maps;

	/**
	 * @var ReflectionProperty[]
	 */
	private $refp;

	/**
	 * Register a mapping
	 * @param MapInterface $map
	 * @return Mapper
	 */
	function register(MapInterface $map) {
		$this->maps[$map->getClass()] = $map;
		return $this;
	}

	/**
	 * Get a property reflector
	 * @param string $class
	 * @param string $prop
	 * @return ReflectionProperty
	 */
	function getReflector($class, $prop) {
		if (is_object($class)) {
			$class = get_class($class);
		}
		$hash = "$class::$prop";
		if (!isset($this->refp[$hash])) {
			$this->refp[$hash] = new ReflectionProperty($class, $prop);
			$this->refp[$hash]->setAccessible(true);
		}
		return $this->refp[$hash];
	}

	/**
	 * Get the mapping of $class
	 * @param string $class
	 * @return MapInterface
	 * @throws UnexpectedValueException
	 */
	function mapOf($class) {
		if (is_object($class)) {
			$class = get_class($class);
		}
		if (!isset($this->maps[$class])) {
			if (!is_callable([$class, "mapAs"])) {
				throw new UnexpectedValueException("Not a mapped class: '$class'");
			}
			$this->register($class::mapAs($this));
		}
		return $this->maps[$class];
	}

	/**
	 * Create a storage for $class
	 * @param string $class
	 * @return Storage
	 */
	function createStorage($class) {
		return new Storage($this->mapOf($class));
	}

	/**
	 * Create a simple field mapping
	 * @param string $property
	 * @param string $field
	 * @return Field
	 */
	function mapField($property, $field = null) {
		return new Field($this, $property, $field);
	}

	/**
	 * Create a child rows mapping by foreign key
	 * @param string $property
	 * @return All
	 */
	function mapAll($property) {
		return new All($this, $property);
	}

	/**
	 * Create a parent row mapping by foreign key
	 * @param string $property
	 * @return Ref
	 */
	function mapRef($property) {
		return new Ref($this, $property);
	}
}
