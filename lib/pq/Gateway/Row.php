<?php

namespace pq\Gateway;

class Row
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
	 */
	function __construct(Table $table, array $data = null) {
		$this->table = $table;
		$this->data = $data;
	}
	
	function __get($p) {
		if (!isset($this->mod[$p])) {
			$this->mod[$p] = new Cell($this, $p);
		}
		return $this->mod[$p];
	}
	
	function create() {
		$this->data = $this->table->create($this->mods)->getIterator()->current()->data;
		$this->mods = array();
		return $this;
	}
	
	function update() {
		$this->data = $this->table->update($this->data, $this->mods)->getIterator()->current()->data;
		$this->mods = array();
		return $this;
	}
	
	function delete() {
		$this->data = $this->table->delete($this->data, "*")->getIterator()->current()->data;
		$this->mods = array();
		return $this;
	}
}
