<?php

namespace pq\Gateway\Table;

use \pq\Gateway\Table;

const RELATION_SQL = <<<SQL
select
	 cl1.relname  as "foreignTable"
	,array(
		select 
			 attname
			from pg_attribute,
			generate_subscripts(conkey,1) index
		where 
			attrelid = cl1.oid 
		and attnum   = any(conkey) 
		and conkey[index] = attnum
		order by
			 index
	)             as "foreignColumns"
	,cl2.relname  as "referencedTable"
	,array(
		select 
			 attname
			from pg_attribute,
			generate_subscripts(confkey,1) index
		where 
			attrelid = cl2.oid 
		and attnum   = any(confkey) 
		and confkey[index] = attnum
		order by
			 index
	)             as "referencedColumns"
from pg_constraint co
join pg_class      cl1 on cl1.oid = co.conrelid
join pg_class      cl2 on cl2.oid = co.confrelid
where
	 cl1.relname  = \$1
and co.contype    = 'f'
and co.confrelid != 0
SQL;

/**
 * Foreign key list
 */
class Relations implements \Countable, \IteratorAggregate
{
	/**
	 * @var array
	 */
	protected $references;
	
	/**
	 * @param \pq\Gateway\Table $table
	 */
	function __construct(Table $table) {
		$cache = $table->getMetadataCache();
		if (!($this->references = $cache->get("$table:relations"))) {
			$table->getQueryExecutor()->execute(
				new \pq\Query\Writer(RELATION_SQL, array($table->getName())),
				function($result) use($table, $cache) {
					$rel = $result->map([1,2], null, \pq\Result::FETCH_ASSOC);
					foreach ($rel as $ref) {
						foreach ($ref as $table => $key) {
							$reference = new Reference($key);
							$this->references[$table][$reference->name] = $reference;
						}
					}
					$cache->set("$table:relations", $this->references);
				}
			);
		}
	}
	
	function __isset($r) {
		return isset($this->references[$r]);
	}
	
	function __get($r) {
		return $this->references[$r];
	}
	
	function __set($r, $v) {
		$this->references[$r] = $v;
	}
	
	function __unset($r){
		unset($this->references[$r]);
	}
	
	/**
	 * Get a reference to a table
	 * @param string $table
	 * @param string $ref
	 * @return \pq\Gateway\Table\Reference
	 */
	function getReference($table, $ref = null) {
		if (isset($this->references[$table])) {
			if (!strlen($ref)) {
				return current($this->references[$table]);
			}
			if (isset($this->references[$table][$ref])) {
				return $this->references[$table][$ref];
			}
		}
	}
	
	/**
	 * Implements \Countable
	 * @return int
	 */
	function count() {
		return array_sum(array_map("count", $this->references));
	}
	
	/**
	 * Implements \IteratorAggregate
	 * @return \ArrayIterator
	 */
	function getIterator() {
		return new \ArrayIterator($this->references);
	}
}
