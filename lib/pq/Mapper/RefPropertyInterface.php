<?php

namespace pq\Mapper;

interface RefPropertyInterface extends PropertyInterface
{
	/**
	 * Define the referred class
	 * @param string $class
	 * @return RefPropertyInterface
	 */
	function to($class);

	/**
	 * Check whether this mapping refers to $class
	 * @param string $class
	 * @return bool
	 */
	function references($class);

	/**
	 * Define the foreign key name as defined by pq\Gateway\Table\Reference
	 * @param string $ref
	 * @return RefPropertyInterface
	 */
	function by($ref);

	/**
	 * Check whether this mapping referes to a foreign key
	 * @param string $ref
	 * @return bool
	 */
	function on($ref);
}
