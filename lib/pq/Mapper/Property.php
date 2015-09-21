<?php

namespace pq\Mapper;

trait Property
{
	/**
	 *
	 * @var Mapper
	 */
	private $mapper;

	/**
	 * @var string
	 */
	private $field;

	/**
	 * @var string
	 */
	private $property;

	/**
	 * Set the containing map
	 * @param MapInterface $container
	 * @return Property
	 */
	function setContainer(MapInterface $container) {
		$this->container = $container;
		return $this;
	}

	/**
	 * Get the containing map
	 * @return MapInterface
	 */
	function getContainer() {
		return $this->container;
	}

	/**
	 * Get the property name
	 * @return string
	 */
	function getProperty() {
		return $this->property;
	}

	/**
	 * Check whether this Property defines $property
	 * @param string $property
	 * @return bool
	 */
	function defines($property) {
		return $this->property === $property;
	}

	/**
	 * Check whether this property exposes $field
	 * @param string $field
	 * @return bool
	 */
	function exposes($field) {
		return $this->field === $field;
	}

	/**
	 * Set the value of the mapped property
	 * @param object $object
	 * @param mixed $value
	 */
	function assign($object, $value) {
		$this->mapper
			->getReflector($object, $this->property)
			->setValue($object, $value);
	}

	/**
	 * Get the value of the mapped property
	 * @param object $object
	 * @return mixed
	 */
	function extract($object) {
		return $this->mapper
			->getReflector($object, $this->property)
			->getValue($object);
	}

	/**
	 * @ignore
	 */
	function __toString() {
		return sprintf("%s: %s(%s)", get_class($this), $this->property, $this->field?:"NULL");
	}
}
