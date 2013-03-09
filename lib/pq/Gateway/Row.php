<?php

namespace pq\Gateway;

class Row implements \JsonSerializable
{
	/**
	 * @var \pq\Gateway\Table
	 */
	protected $table;
	
	/**
	 * @var array
	 */
	protected $data;
	
	/**
	 * @var array
	 */
	protected $cell = array();
	
	/**
	 * @param \pq\Gateway\Table $table
	 * @param array $data
	 * @param bool $prime whether to mark all columns as modified
	 */
	function __construct(Table $table, array $data = null, $prime = false) {
		$this->table = $table;
		$this->data = (array) $data;
		
		if ($prime) {
			$this->prime();
		}
	}
	
	/**
	 * Copy constructor
	 * @param array $data
	 * @return \pq\Gateway\Row
	 */
	function __invoke(array $data) {
		$that = clone $this;
		$that->data = $data;
		return $that->prime();
	}
	
	/**
	 * @implements JsonSerializable
	 * @return array
	 */
	function jsonSerialize() {
		return $this->data;
	}
	
	/**
	 * @return \pq\Gateway\Table
	 */
	function getTable() {
		return $this->table;
	}
	
	/**
	 * @return array
	 */
	function getData() {
		return $this->data;
	}
	
	/**
	 * Check whether the row contains modifications
	 * @return boolean
	 */
	function isDirty() {
		foreach ($this->cell as $cell) {
			if ($cell->isDirty()) {
				return true;
			}
		}
		return false;
	}
	
	function refresh() {
		$this->data = $this->table->find($this->criteria(), null, 1, 0)->current()->data;
		$this->cell = array();
		return $this;
	}
	
	/**
	 * Fill modified cells
	 * @return \pq\Gateway\Row
	 */
	protected function prime() {
		$this->cell = array();
		foreach ($this->data as $key => $val) {
			$this->cell[$key] = new Cell($this, $key, $val, true);
		}
		return $this;
	}
	
	/**
	 * Transform data array to where criteria
	 * @return array
	 */
	protected function criteria() {
		$where = array();
		foreach($this->data as $k => $v) {
			$where["$k="] = $v;
		}
		return $where;
	}
	
	/**
	 * Get an array of changed properties
	 * @return array
	 */
	protected function changes() {
		$changes = array();
		foreach ($this->cell as $name => $cell) {
			if ($cell->isDirty()) {
				$changes[$name] = $cell->get();
			}
		}
		return $changes;
	}
	
	/**
	 * Get a cell
	 * @param string $p
	 * @return \pq\Gateway\Cell
	 */
	function __get($p) {
		if (!isset($this->cell[$p])) {
			$this->cell[$p] = new Cell($this, $p, isset($this->data[$p]) ? $this->data[$p] : null);
		}
		return $this->cell[$p];
	}
	
	/**
	 * Set a cell value
	 * @param string $p
	 * @param mixed $v
	 */
	function __set($p, $v) {
		$this->__get($p)->set(($v instanceof Cell) ? $v->get() : $v);
	}
	
	/**
	 * Create this row in the database
	 * @return \pq\Gateway\Row
	 */
	function create() {
		$this->data = $this->table->create($this->changes())->current()->data;
		$this->cell = array();
		return $this;
	}
	
	/**
	 * Update this row in the database
	 * @return \pq\Gateway\Row
	 */
	function update() {
		$this->data = $this->table->update($this->criteria(), $this->changes())->current()->data;
		$this->cell = array();
		return $this;
	}
	
	/**
	 * Delete this row in the database
	 * @return \pq\Gateway\Row
	 */
	function delete() {
		$this->data = $this->table->delete($this->criteria(), "*")->current()->data;
		return $this->prime();
	}
}
