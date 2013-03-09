<?php

namespace pq\Query;

class Expressible implements ExpressibleInterface
{
	/**
	 * @var mixed
	 */
	protected $data;
	
	function __construct($data) {
		$this->data = $data;
	}
	
	/**
	 * Get value as string
	 * @return string
	 */
	function __toString() {
		return (string) $this->data;
	}
	
	/**
	 * Test whether the value is an unevaluated expression
	 * @return bool
	 */
	function isExpr() {
		return $this->data instanceof Expr;
	}

	/**
	 * Get value
	 * @return mixed
	 */
	function get() {
		return $this->data;
	}
	
	/**
	 * Set the value
	 * @param mixed $data
	 * @return \pq\Query\Expressible
	 */
	function set($data) {
		$this->data = $data;
		return $this;
	}
	
	/**
	 * Modify the data
	 * @param mixed $data
	 * @param string $op a specific operator
	 * @return \pq\Query\Expressible
	 */
	function mod($data, $op = null) {
		if (!($this->data instanceof Expr)) {
			$this->data = new Expr($this->name);
		}
		
		if ($data instanceof Expr) {
			$this->data->add($data);
		} elseif (!isset($op) && is_numeric($data)) {
			$this->data->add(new Expr("+ $data"));
		} else {
			$data = $this->row->getTable()->getConnection()->quote($data);
			$this->data->add(new Expr("%s %s", isset($op) ? $op : "||", $data));
		}
		
		return $this;
	}		
}