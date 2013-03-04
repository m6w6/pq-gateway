<?php

namespace pq\Gateway;

class Rowset implements \IteratorAggregate
{
	/**
	 * @var \pq\Gateway\Table
	 */
	protected $table;
	
	/**
	 * @var array
	 */
	protected $rows;
	
	/**
	 * @param \pq\Gateway\Table $table
	 * @param \pq\Result $result
	 */
	function __construct(Table $table, \pq\Result $result, $rowClass = "\\pq\\Gateway\\Row") {
		$this->table = $table;
		while (($row = $result->fetchRow(\pq\Result::FETCH_ASSOC))) {
			$this->rows[] = new $rowClass($this->table, $row);
		}
	}
	
	/**
	 * @implements \IteratorAggregate
	 * @return \pq\Gateway\ArrayIterator
	 */
	function getIterator() {
		return new \ArrayIterator($this->rows);
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
