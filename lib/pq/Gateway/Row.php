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
	protected $mods = array();
	
	/**
	 * @param \pq\Gateway\Table $table
	 * @param array $data
	 * @param bool $prime whether to mark all columns as modified
	 */
	function __construct(Table $table, array $data = null, $prime = false) {
		$this->table = $table;
		$this->data = $data;
		
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
	 * Fill modified cells
	 * @return \pq\Gateway\Row
	 */
	protected function prime() {
		$this->mods = array();
		foreach ($this->data as $key => $val) {
			$this->mods[$key] = new Cell($this, $key, $val);
		}
		return $this;
	}
	
	/**
	 * Transform data array to where criteria
	 * @param array $data
	 * @return array
	 */
	protected function criteria() {
		$where = array();
		array_walk($this->data, function($v, $k) use (&$where) {
			$where["$k="] = $v;
		});
		return $where;
	}
	
	protected function changes() {
		$changes = array();
		foreach ($this->mods as $name => $cell) {
			$changes[$name] = $cell->get();
		}
		return $changes;
	}
	
	/**
	 * Get a cell
	 * @param string $p
	 * @return \pq\Gateway\Cell
	 */
	function __get($p) {
		if (!isset($this->mods[$p])) {
			$this->mods[$p] = new Cell($this, $p, $this->data[$p]);
		}
		return $this->mods[$p];
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
		$this->mods = array();
		return $this;
	}
	
	/**
	 * Update this row in the database
	 * @return \pq\Gateway\Row
	 */
	function update() {
		$this->data = $this->table->update($this->criteria(), $this->changes())->current()->data;
		$this->mods = array();
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
