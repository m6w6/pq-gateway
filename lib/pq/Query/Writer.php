<?php

namespace pq\Query;

/**
 * A very simple query writer used by \pq\Gateway
 */
class Writer
{
	/**
	 * @var string
	 */
	protected $query;
	
	/**
	 * @var array
	 */
	protected $params;
	
	/**
	 * @var array
	 */
	protected $types;

	/**
	 * @param string $query initial query string
	 * @param array $params intial set of params
	 * @param array $types the types of the params
	 */
	function __construct($query = "", array $params = array(), array $types = array()) {
		$this->query = $query;
		$this->params = $params;
		$this->types = $types;
	}

	/**
	 * Get the query string
	 * @return string
	 */
	function __toString() {
		return $this->query;
	}

	/**
	 * Get the query params
	 * @return array
	 */
	function getParams() {
		return $this->params;
	}

	/**
	 * Get the param types
	 * @return array
	 */
	function getTypes() {
		return $this->types;
	}

	/**
	 * Reset
	 * @return \pq\Query\Writer
	 */
	function reset() {
		$this->query = "";
		$this->params = array();
		$this->types = array();
		return $this;
	}

	/**
	 * Append to the query string
	 * @return \pq\Query\Writer
	 */
	function write() {
		$this->query .= array_reduce(func_get_args(), function($q, $v) {
			return $q . " " . (is_array($v) ? implode(", ", $v) : $v);
		});
		return $this;
	}

	/**
	 * Write a param placeholder and push the param onto the param list
	 * @param mixed $param
	 * @param string $type
	 * @return string
	 */
	function param($param, $type = null) {
		if ($param instanceof Expr) {
			return (string) $param;
		}
		$this->params[] = $param;
		$this->types[] = $type;
		return "\$".count($this->params);
	}

	/**
	 * Execute the query through \pq\Connection::execParams($this, $this->params, $this->types)
	 * @param \pq\Connection $c
	 * @return \pq\Result
	 */
	function exec(\pq\Connection $c) {
		return $c->execParams($this, $this->params, $this->types);
	}
}
