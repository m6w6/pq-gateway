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
	 * @var string
	 */
	protected $row;
	
	/**
	 * @param \pq\Gateway\Table $table
	 * @param \pq\Result $result
	 */
	function __construct(Table $table, \pq\Result $result, $row = "\\pq\\Gateway\\Row") {
		$this->table = $table;
		$this->row   = $row;
		
		$this->hydrate($result);
	}
	
	/**
	 * Copy constructor
	 * @param \pq\Result $result
	 * @return \pq\Gateway\Rowset
	 */
	function __invoke(\pq\Result $result) {
		$that = clone $this;
		$that->hydrate($result);
		return $that;
	}
	
	/**
	 * 
	 * @param \pq\Result $result
	 * @return array
	 */
	protected function hydrate(\pq\Result $result) {
		$this->index = 0;
		$this->rows  = array();
		$row = $this->row;
		
		if (is_callable($row)) {
			while (($data = $result->fetchRow(\pq\Result::FETCH_ASSOC))) {
				$this->rows[] = $row($data);
			}
		} else {
			while (($data = $result->fetchRow(\pq\Result::FETCH_ASSOC))) {
				$this->rows[] = new $row($this->table, $data);
			}
		}
		return $this;
	}
	
	/**
	 * @return \pq\Gateway\Table
	 */
	function getTable() {
		return $this->table;
	}
	
	function create() {
		array_map(function ($row) {
			$row->create();
		}, $this->rows);
		return $this;
	}
	
	function update() {
		array_map(function ($row) {
			$row->update();
		}, $this->rows);
		return $this;
	}
	
	function delete() {
		array_map(function ($row) {
			$row->delete();
		}, $this->rows);
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
	 * Filter by callback
	 * @param callable $cb
	 * @return \pq\Gateway\Rowset
	 */
	function filter(callable $cb) {
		$rowset = clone $this;
		$rowset->rows = array_filter($this->rows, $cb);
		return $rowset;
	}
}
