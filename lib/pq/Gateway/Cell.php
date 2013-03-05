<?php

namespace pq\Gateway;

use \pq\Query\Expr;

class Cell
{
	/**
	 * @var \pq\Gateway\Row
	 */
	protected $row;
	
	/**
	 * @var string
	 */
	protected $name;
	
	/**
	 * @var mixed
	 */
	protected $data;
	
	/**
	 * @var bool
	 */
	protected $dirty;
	
	/**
	 * @param \pq\Gateway\Row $row
	 * @param string $name
	 * @param mixed $data
	 * @param bool $dirty
	 */
	function __construct(Row $row, $name, $data, $dirty = false) {
		$this->row = $row;
		$this->name = $name;
		$this->data = $data;
		$this->dirty = $dirty;
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
	 * Check whether the cell has been modified
	 * @return bool
	 */
	function isDirty() {
		return $this->dirty;
	}
	
	/**
	 * Get value
	 * @return mixed
	 */
	function get() {
		return $this->data;
	}
	
	/**
	 * Modify the value in this cell
	 * @param mixed $data
	 * @param string $op a specific operator
	 * @return \pq\Gateway\Cell
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
			$this->data->add(new Expr("%s %s"), isset($op) ? $op : "||", $data);
		}
		
		$this->dirty = true;
		
		return $this;
	}
	
	/**
	 * Set the value in this cell
	 * @param mixed $data
	 * @return \pq\Gateway\Cell
	 */
	function set($data) {
		$this->data = $data;
		$this->dirty = true;
		return $this;
	}
}
