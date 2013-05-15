<?php

namespace pq\Gateway\Table;

use \pq\Gateway\Table;

const ATTRIBUTES_SQL = <<<SQL
	select 
		 attnum         as index
		,attname        as name
		,atttypid       as type
		,atthasdef      as hasdefault
		,not attnotnull as nullable
	from
		 pg_attribute 
	where attrelid = \$1::regclass 
	and   attnum   > 0
SQL;

class Attributes
{
	/**
	 * @var array
	 */
	protected $columns = array();
	
	/**
	 * @param \pq\Gateway\Table $table
	 */
	function __construct(Table $table) {
		$cache = $table->getMetadataCache();
		if (!($this->columns = $cache->get("$table#attributes"))) {
			$table->getQueryExecutor()->execute(
				new \pq\Query\Writer(ATTRIBUTES_SQL, array($table->getName())), 
				function($result) use($table, $cache) {
					foreach ($result->fetchAll(\pq\Result::FETCH_OBJECT) as $c) {
						$this->columns[$c->index] = $this->columns[$c->name] = $c;
					}
					$cache->set("$table#attributes", $this->columns);
				}
			);
		}
	}
	
	/**
	 * @implements \Countable
	 * @return int
	 */
	function count() {
		return count($this->columns);
	}
	
	/**
	 * Get all columns
	 * @return array
	 */
	function getColumns() {
		return $this->columns;
	}
	
	/**
	 * Get a single column
	 * @param string $c
	 * @return object
	 */
	function getColumn($c) {
		if (!isset($this->columns[$c])) {
			throw new \OutOfBoundsException("Unknown column $c");
		}
		return $this->columns[$c];
	}
}
