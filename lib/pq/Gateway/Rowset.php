<?php

namespace pq\Gateway;

class Rowset implements \SeekableIterator, \Countable, \JsonSerializable
{
	/**
	 * @var \pq\Gateway\Table
	 */
	protected $table;
	
	/**
	 * @var int
	 */
	protected $index = 0;
	
	/**
	 * @var array
	 */
	protected $rows;
	
	/**
	 * @var mixed
	 */
	protected $row = "\\pq\\Gateway\\Row";
	
	/**
	 * @param \pq\Gateway\Table $table
	 * @param \pq\Result $result
	 */
	function __construct(Table $table, \pq\Result $result = null) {
		$this->table = $table;
		$this->hydrate($result);
	}
	
	/**
	 * Copy constructor
	 * @param \pq\Result $result
	 * @return \pq\Gateway\Rowset
	 */
	function __invoke(\pq\Result $result = null) {
		$that = clone $this;
		$that->hydrate($result);
		return $that;
	}
	
	/**
	 * 
	 * @param \pq\Result $result
	 * @return array
	 */
	protected function hydrate(\pq\Result $result = null) {
		$this->index = 0;
		$this->rows  = array();
		
		if ($result) {
			$row = $this->getRowPrototype();

			if (is_callable($row)) {
				while (($data = $result->fetchRow(\pq\Result::FETCH_ASSOC))) {
					$this->rows[] = $row($data);
				}
			} elseif ($row) {
				while (($data = $result->fetchRow(\pq\Result::FETCH_ASSOC))) {
					$this->rows[] = new $row($this->table, $data);
				}
			} else {
				$this->rows = $result->fetchAll(\pq\Result::FETCH_OBJECT);
			}
		}
		
		return $this;
	}
	
	/**
	 * Set the row prototype
	 * @param mixed $row
	 * @return \pq\Gateway\Table
	 */
	function setRowPrototype($row) {
		$this->row = $row;
		return $this;
	}
	
	/**
	 * Get the row prototype
	 * @return mixed
	 */
	function getRowPrototype() {
		return $this->row;
	}
	
	/**
	 * @return \pq\Gateway\Table
	 */
	function getTable() {
		return $this->table;
	}
	
	/**
	 * Create all rows of this rowset
	 * @param bool $txn
	 * @return \pq\Gateway\Rowset
	 * @throws Exception
	 */
	function create($txn = true) {
		$txn = $txn ? $this->table->getConnection()->startTransaction() : false;
		try {
			foreach ($this->rows as $row) {
				$row->create();
			}
		} catch (\Exception $e) {
			if ($txn) {
				$txn->rollback();
			}
			throw $e;
		}
		if ($txn) {
			$txn->commit();
		}
		return $this;
	}
	
	/**
	 * Update all rows of this rowset
	 * @param bool $txn
	 * @return \pq\Gateway\Rowset
	 * @throws \Exception
	 */
	function update($txn = true) {
		$txn = $txn ? $this->table->getConnection()->startTransaction() : false;
		try {
			foreach ($this->rows as $row) {
				$row->update();
			}
		} catch (\Exception $e) {
			if ($txn) {
				$txn->rollback();
			}
			throw $e;
		}
		if ($txn) {
			$txn->commit();
		}
		return $this;
	}
	
	/**
	 * Delete all rows of this rowset
	 * @param type $txn
	 * @return \pq\Gateway\Rowset
	 * @throws \Exception
	 */
	function delete($txn = true) {
		$txn = $txn ? $this->table->getConnection()->startTransaction() : false;
		try {
			foreach ($this->rows as $row) {
				$row->delete();
			}
		} catch (\Exception $e) {
			if ($txn) {
				$txn->rollback();
			}
			throw $e;
		}
		if ($txn) {
			$txn->commit();
		}
		return $this;		
	}
	
	/**
	 * @implements JsonSerilaizable
	 */
	function jsonSerialize() {
		return array_map(function($row) {
			return $row->jsonSerialize();
		}, $this->rows);
	}
	
	/**
	 * @implements \Iterator
	 */
	function rewind() {
		$this->index = 0;
	}
	
	/**
	 * @implements \Iterator
	 */
	function next() {
		++$this->index;
	}
	
	/**
	 * @implements \Iterator
	 * @return bool
	 */
	function valid() {
		return $this->index < count($this->rows);
	}
	
	/**
	 * @implements \Iterator
	 * @return \pq\Gateway\Row
	 */
	function current() {
		if (!$this->valid()) {
			throw new OutOfBoundsException("Invalid row index {$this->index}");
		}
		return $this->rows[$this->index];
	}
	
	/**
	 * @implements \Iterator
	 * @return int
	 */
	function key() {
		return $this->index;
	}
	
	/**
	 * @implements SeekableIterator
	 * @param mixed $pos
	 */
	function seek($pos) {
		/* only index for now */
		$this->index = $pos;
      
		if (!$this->valid()) {
			throw new \OutOfBoundsException("Invalid seek position ($pos)");
		}
		
		return $this;
	}
	
	/**
	 * @implements \Countable
	 * @return int
	 */
	function count() {
		return count($this->rows);
	}
	
	/**
	 * Get the rows of this rowset
	 * @return array
	 */
	function getRows() {
		return $this->rows;
	}
	
	/**
	 * Apply a callback on each row of this rowset
	 * @param callable $cb
	 * @return \pq\Gateway\Rowset
	 */
	function apply(callable $cb) {
		array_walk($this->rows, $cb, $this);
		return $this;
	}
	
	/**
	 * Filter by callback
	 * @param callable $cb
	 * @return \pq\Gateway\Rowset
	 */
	function filter(callable $cb) {
		$rowset = clone $this;
		$rowset->index = 0;
		$rowset->rows = array_filter($this->rows, $cb);
		return $rowset;
	}
	
	/**
	 * Append a row to the rowset
	 * @param \pq\Gateway\Row $row
	 */
	function append(Row $row) {
		$this->rows[] = $row;
		return $this;
	}
}
