<?php

namespace pq\Query;

interface ExpressibleInterface
{
	/**
	 * Get the contained value as string
	 * @return string
	 */
	function __toString();
	
	/**
	 * Test whether we are an expression
	 * @return bool
	 */
	function isExpr();
	
	/**
	 * Get the literal value or the expression
	 * @return mixed
	 */
	function get();
	
	/**
	 * Set the contained value
	 * @param mixed $data
	 * @return \pq\Query\ExpressibleInterface
	 */
	function set($data);
	
	/**
	 * Modify the data
	 * @param mixed $data
	 * @param string $op a specific operator
	 * @return \pq\Query\ExpressibleInterface
	 */
	function mod($data, $op = null);

}
