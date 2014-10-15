<?php

namespace pq\Gateway;

use \pq\Query\Expressible;

class Cell extends Expressible implements \ArrayAccess
{
	/**
	 * @var \pq\Gateway\Row
	 */
	protected $row;
	
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
		parent::__construct($name, $data);
		$this->row = $row;
		$this->dirty = $dirty;
	}
	
	/**
	 * Check whether the cell has been modified
	 * @return bool
	 */
	function isDirty() {
		return (bool) $this->dirty;
	}
	
	/**
	 * Set the value
	 * @param mixed $data
	 * @return \pq\Gateway\Cell
	 */
	function set($data) {
		if ($data instanceof Row) {
			$this->row->__set($data->getTable()->getName() . "_id", $data->id);
			$this->row->__unset($this->name);
			return $this;
		}
		if ($data instanceof Cell) {
			$data = $data->get();
		}
		parent::set($data);
		$this->dirty = true;
		return $this;
	}
	
	/**
	 * Modify the value in this cell
	 * @param mixed $data
	 * @param string $op a specific operator
	 * @return \pq\Gateway\Cell
	 */
	function mod($data, $op = null) {
		if (is_string($data)) {
			$data = $this->row->getTable()->getConnection()->quote($data);
		}
		parent::mod($data, $op);
		$this->dirty = true;
		return $this;
	}
	
	function offsetGet($o) {
		if (isset($this->data) && !is_array($this->data)) {
			throw new \UnexpectedValueException("Cell data is not an array");
		}
		return $this->data[$o];
	}

	function offsetSet($o, $v) {
		if (isset($this->data) && !is_array($this->data)) {
			throw new \UnexpectedValueException("Cell data is not an array");
		}
		if (isset($o)) {
			$this->data[$o] = $v;
		} else {
			$this->data[] = $v;
		}
		$this->dirty = true;
	}

	function offsetExists($o) {
		if (isset($this->data) && !is_array($this->data)) {
			throw new \UnexpectedValueException("Cell data is not an array");
		}
		return isset($this->data[$o]);
	}

	function offsetUnset($o) {
		if (isset($this->data) && !is_array($this->data)) {
			throw new \UnexpectedValueException("Cell data is not an array");
		}
		unset($this->data[$o]);
		$this->dirty = true;
	}
}
