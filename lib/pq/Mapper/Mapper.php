<?php

namespace pq\Mapper;

use UnexpectedValueException;

class Mapper
{
	private $maps;
	private $refp;

	/**
	 * @param \pq\Mapper\MapInterface $map
	 * @return \pq\Mapper\Mapper
	 */
	function register(MapInterface $map) {
		$this->maps[$map->getClass()] = $map;
		return $this;
	}

	function getReflector($class, $prop) {
		if (is_object($class)) {
			$class = get_class($class);
		}
		$hash = "$class::$prop";
		if (!isset($this->refp[$hash])) {
			$this->refp[$hash] = new \ReflectionProperty($class, $prop);
			$this->refp[$hash]->setAccessible(true);
		}
		return $this->refp[$hash];
	}

	/**
	 * @param string $class
	 * @return \pq\Mapper\MapInterface
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
	 * @param string $class
	 * @return \pq\Mapper\Storage
	 */
	function createStorage($class) {
		return new Storage($this, $class);
	}

	/**
	 * @param string $property
	 * @param string $field
	 * @return \pq\Mapper\Property\Field
	 */
	function mapField($property, $field = null) {
		return new Property\Field($this, $property, $field);
	}

	/**
	 * @param string $property
	 * @return \pq\Mapper\Property\All
	 */
	function mapAll($property) {
		return new Property\All($this, $property);
	}

	/**
	 * @param string $property
	 * @return \pq\Mapper\Property\Ref
	 */
	function mapRef($property) {
		return new Property\Ref($this, $property);
	}
}
