<?php

namespace pq\Mapper;

interface RefPropertyInterface extends PropertyInterface
{
	/**
	 * @param string $class
	 */
	function to($class);

	/**
	 * @param string $class
	 * @return bool
	 */
	function references($class);

	/**
	 * @param string $ref
	 */
	function by($ref);

	/**
	 * @param string $ref
	 * @return bool
	 */
	function on($ref);
}
