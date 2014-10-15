<?php

namespace pq\Query;

/**
 * A query writer which supports easily constructing queries for \pq\Connection::execParams()
 * @codeCoverageIgnore
 */
interface WriterInterface
{
	/**
	 * Returns the plain constructed query as string
	 * @return string
	 */
	function __toString();
	
	/**
	 * Returns a list of parameters as array
	 * @return array
	 */
	function getParams();
	
	/**
	 * Returns a list any types associated with the params
	 * @return array
	 */
	function getTypes();
	
	/**
	 * Reset the state of the query writer
	 * @return \pq\Query\WriterInterface
	 */
	function reset();
	
	/**
	 * Write plain SQL to the query
	 * @param mixed $arg variable list of arguments, arrays will be imploded to a comma separated list
	 * @return \pq\Query\WriterInterface
	 */
	function write(/*...*/);
	
	/**
	 * Remember the parameter with any associated type and return $N to be written to the query string
	 * @param mixed $param a literal parameter, a \pq\Gateway\Table\Cell or a \pq\Query\Expr
	 * @param int $type the oid of the type of the param
	 * @return \pq\Query\WriterInterface
	 */
	function param($param, $type = null);
	
	/**
	 * An array of AND/OR criteria
	 * @param array $criteria
	 */
	function criteria(array $criteria);
}
