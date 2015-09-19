<?php

namespace pq\Mapper;

trait Property
{
	private $mapper;
	private $field;
	private $property;

	function setContainer(MapInterface $container) {
		$this->container = $container;
	}

	function getContainer() {
		return $this->container;
	}

	function getProperty() {
		return $this->property;
	}

	function defines($property) {
		return $this->property === $property;
	}

	function exposes($field) {
		return $this->field === $field;
	}

	function assign($object, $value) {
		$this->mapper
			->getReflector($object, $this->property)
			->setValue($object, $value);
	}

	function extract($object) {
		return $this->mapper
			->getReflector($object, $this->property)
			->getValue($object);
	}

	function __toString() {
		return sprintf("%s: %s(%s)", get_class($this), $this->property, $this->field?:"NULL");
	}
}