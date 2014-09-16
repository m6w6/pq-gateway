<?php

namespace pq\Gateway\Table;

use \pq\Gateway\Row;

/**
 * An optimistic row lock implementation using a versioning column
 */
class OptimisticLock implements \SplObserver
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
	 * @param \pq\Gateway\Table $table
	 * @param \pq\Gateway\Row $row
	 * @param string $event create/update/delete
	 * @param array $where reference to the criteria
	 */
	function update(\SplSubject $table, Row $row = null, $event = null, array &$where = null) {
		if ($event === "update") {
			$where["{$this->column}="] = $row->getData()[$this->column];
			$row->{$this->column}->mod(+1);
		}
	}
}
