<?php

namespace pq\Mapper;

trait RefProperty
{
	use Property;

	/**
	 * The referred class
	 * @var string
	 */
	private $refClass;

	/**
	 * The foreign key name
	 * @var string
	 */
	private $refName;

	/**
	 * Define the referred class
	 * @param string $class
	 * @return RefPropertyInterface
	 */
	function to($class) {
		$this->refClass = $class;
		return $this;
	}

	/**
	 * Check whether this mapping refers to $class
	 * @param string $class
	 * @return bool
	 */
	function references($class) {
		return $this->refClass === (is_object($class) ? get_class($class) : $class);
	}

	/**
	 * Define the foreign key name as defined by pq\Gateway\Table\Reference
	 * @param string $ref
	 * @return RefPropertyInterface
	 */
	function by($ref) {
		$this->refName = $ref;
		return $this;
	}

	/**
	 * Check whether this mapping referes to a foreign key
	 * @param string $ref
	 * @return bool
	 */
	function on($ref) {
		return $this->refName === $ref;
	}
}
