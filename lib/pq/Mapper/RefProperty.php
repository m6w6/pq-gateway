<?php

namespace pq\Mapper;

trait RefProperty
{
	use Property;

	private $refClass;
	private $refName;

	function to($class) {
		$this->refClass = $class;
		return $this;
	}

	function references($class) {
		return $this->refClass === (is_object($class) ? get_class($class) : $class);
	}

	function by($ref) {
		$this->refName = $ref;
		return $this;
	}

	function on($ref) {
		return $this->refName === $ref;
	}

}
