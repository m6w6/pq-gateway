<?php

namespace pq\Gateway;

use \pq\Query\Expr as QueryExpr;

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
	 * Export current state as an array
	 * @return array
	 * @throws \UnexpectedValueException if a cell has been modified by an expression
	 */
	function export() {
		$export = array_merge($this->data, $this->cell);
		foreach ($export as &$val) {
			if ($val instanceof Cell) {
				if ($val->isExpr()) {
					throw new \UnexpectedValueException("Cannot export an SQL expression");
				}
				$val = $val->get();
			}
		}
		return $export;
	}
	
	/**
	 * Export current state with security sensitive data removed. You should override that.
	 * Just calls export() by default.
	 * @return array
	 */
	function exportPublic() {
		return $this->export();
	}

	/**
	 * @implements JsonSerializable
	 * @return array
	 */
	function jsonSerialize() {
		return $this->exportPublic();
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
	 * Get all column/value pairs to possibly uniquely identify this row
	 * @return array
	 * @throws \OutOfBoundsException if any primary key column is not present in the row
	 */
	function getIdentity() {
		$cols = array();
		if (count($identity = $this->getTable()->getIdentity())) {
			foreach ($identity as $col) {
				if (!array_key_exists($col, $this->data)) {
					throw new \OutOfBoundsException(
						sprintf("Column '%s' does not exist in row of table '%s'",
							$col, $this->getTable()->getName()
						)
					);
				}
				$cols[$col] = $this->data[$col];
			}
		} else {
			$cols = $this->data;
		}
		return $cols;
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
	
	/**
	 * Refresh the rows data
	 * @return \pq\Gateway\Row
	 */
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
	 * Transform the row's identity to where criteria
	 * @return array
	 */
	protected function criteria() {
		$where = array();
		foreach ($this->getIdentity() as $col => $val) {
			if (isset($val)) {
				$where["$col="] = $val;
			} else {
				$where["$col IS"] = new QueryExpr("NULL");
			}
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
	 * Cell accessor
	 * @param string $p column name
	 * @return \pq\Gateway\Cell
	 */
	protected function cell($p) {
		if (!isset($this->cell[$p])) {
			$this->cell[$p] = new Cell($this, $p, isset($this->data[$p]) ? $this->data[$p] : null);
		}
		return $this->cell[$p];
	}
	
	/**
	 * Get a cell or parent rows
	 * @param string $p
	 * @return \pq\Gateway\Cell|\pq\Gateway\Rowset
	 */
	function __get($p) {
		if ($this->table->hasRelation($p)) {
			return $this->table->by($this, $p);
		}
		return $this->cell($p);
	}
	
	/**
	 * Set a cell value
	 * @param string $p
	 * @param mixed $v
	 */
	function __set($p, $v) {
		$this->cell($p)->set($v);
	}
	
	/**
	 * Unset a cell value
	 * @param string $p
	 */
	function __unset($p) {
		unset($this->data[$p]);
		unset($this->cell[$p]);
	}
	
	/**
	 * Check if a cell isset
	 * @param string $p
	 * @return bool
	 */
	function __isset($p) {
		return isset($this->data[$p]) || isset($this->cell[$p]);
	}
	
	/**
	 * Get child rows of this row by foreign key
	 * @see \pq\Gateway\Table::of()
	 * @param string $foreign
	 * @param array $args [order, limit, offset]
	 * @return \pq\Gateway\Rowset
	 */
	function __call($foreign, array $args) {
		array_unshift($args, $this);
		$table = forward_static_call(array(get_class($this->getTable()), "resolve"), $foreign);
		return call_user_func_array(array($table, "of"), $args);
	}
	
	/**
	 * Create this row in the database
	 * @return \pq\Gateway\Row
	 */
	function create() {
		$rowset = $this->table->create($this->changes());
		if (!count($rowset)) {
			throw new \UnexpectedValueException("No row created");
		}
		$this->data = $rowset->current()->data;
		$this->cell = array();
		return $this;
	}
	
	/**
	 * Update this row in the database
	 * @return \pq\Gateway\Row
	 */
	function update() {
		$criteria = $this->criteria();
		if (($lock = $this->getTable()->getLock())) {
			$lock->onUpdate($this, $criteria);
		}
		$rowset = $this->table->update($criteria, $this->changes());
		if (!count($rowset)) {
			throw new \UnexpectedValueException("No row updated");
		}
		$this->data = $rowset->current()->data;
		$this->cell = array();
		return $this;
	}
	
	/**
	 * Delete this row in the database
	 * @return \pq\Gateway\Row
	 */
	function delete() {
		$rowset = $this->table->delete($this->criteria(), "*");
		if (!count($rowset)) {
			throw new \UnexpectedValueException("No row deleted");
		}
		$this->data = $rowset->current()->data;
		return $this->prime();
	}
}
