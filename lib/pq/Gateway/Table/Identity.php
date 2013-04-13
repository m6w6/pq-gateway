<?php

namespace pq\Gateway\Table;

use \pq\Gateway\Table;

const IDENTITY_SQL = <<<SQL
select
 a.attname as column
from  pg_class     c
 join pg_index     i  on c.oid    = i.indrelid
 join pg_attribute a  on c.oid    = a.attrelid
where 
     c.relname = \$1
 and a.attnum  = any(i.indkey)
 and i.indisprimary
order by 
 a.attnum
SQL;

/**
 * A primary key implementation
 */
class Identity implements \Countable, \IteratorAggregate
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
		if (!($this->columns = $cache->get("$table#identity"))) {
			$table->getQueryExecutor()->execute(
				new \pq\Query\Writer(IDENTITY_SQL, array($table->getName())), 
				function($result) use($table, $cache) {
					$this->columns = array_map("current", $result->fetchAll(\pq\Result::FETCH_ARRAY));
					$cache->set("$table#identity", $this->columns);
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
	 * @implements \IteratorAggregate
	 * @return \ArrayIterator
	 */
	function getIterator() {
		return new \ArrayIterator($this->columns);
	}
	
	/**
	 * Get the column names which the primary key contains
	 * @return array
	 */
	function getColumns() {
		return $this->columns;
	}
}
