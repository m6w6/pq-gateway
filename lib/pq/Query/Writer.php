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
		$this->query  = $query;
		$this->params = $params;
		$this->types  = $types;
	}

	/**
	 * Get the query string
	 * @return string
	 */
	function __toString() {
		return $this->query;
	}
	
	/**
	 * Reduce arguments to write()
	 * @param string $q
	 * @param mixed $v
	 * @return string
	 */
	protected function reduce($q, $v) {
		return $q . " " . (is_array($v) ? implode(", ", $v) : $v);
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
		$this->query  = "";
		$this->params = array();
		$this->types  = array();
		return $this;
	}

	/**
	 * Append to the query string
	 * @return \pq\Query\Writer
	 */
	function write() {
		$this->query .= array_reduce(func_get_args(), array($this, "reduce"));
		return $this;
	}

	/**
	 * Write a param placeholder and push the param onto the param list
	 * @param mixed $param
	 * @param string $type
	 * @return string
	 */
	function param($param, $type = null) {
		if ($param instanceof \pq\Gateway\Cell) {
			$param = $param->get();
		}
		if ($param instanceof Expr) {
			return (string) $param;
		}
		
		$this->params[] = $param;
		$this->types[]  = $type;
		
		return "\$".count($this->params);
	}
	
	/**
	 * Write nested AND/OR criteria
	 * @param array $criteria
	 * @return \pq\Query\Writer
	 */
	function criteria(array $criteria) {
		if ((list($left, $right) = each($criteria))) {
			$this->write("(");
			if (is_array($right)) {
				$this->criteria($right);
			} else {
				$this->write("(", $left, $this->param($right), ")");
			}
			while ((list($left, $right) = each($criteria))) {
				$this->write(is_int($left) && is_array($right) ? "OR" : "AND");
				if (is_array($right)) {
					$this->criteria($right);
				} else {
					$this->write("(", $left, $this->param($right), ")");
				}
			}
			$this->write(")");
		}
		return $this;
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
