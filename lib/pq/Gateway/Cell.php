<?php

namespace pq\Gateway;

use \pq\Query\Expressible;

class Cell extends Expressible
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
		parent::__construct($data);
		$this->row = $row;
		$this->name = $name;
		$this->dirty = $dirty;
	}
	
	/**
	 * Check whether the cell has been modified
	 * @return bool
	 */
	function isDirty() {
		return $this->dirty;
	}
	
	/**
	 * Set the value
	 * @param mixed $data
	 * @return \pq\Gateway\Cell
	 */
	function set($data) {
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
		parent::mod($data, $op);
		$this->dirty = true;
		return $this;
	}
	
}
