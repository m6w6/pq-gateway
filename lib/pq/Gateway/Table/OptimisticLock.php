<?php

namespace pq\Gateway\Table;

use \pq\Gateway\Row;

/**
 * An optimistic row lock implementation using a versioning column
 */
class OptimisticLock implements LockInterface
{
	/**
	 * The name of the versioning column
	 * @var string
	 */
	protected $column;
	
	/**
	 * @param string $column
	 */
	function __construct($column = "version") {
		$this->column = $column;
	}
	
	/**
	 * @implements LockInterface
	 * @param \pq\Gateway\Row $row
	 * @param array $where reference to the criteria
	 */
	function criteria(Row $row, array &$where) {
		$where["{$this->column}="] = $row->getData()[$this->column];
		$row->{$this->column}->mod(+1);
	}
}
