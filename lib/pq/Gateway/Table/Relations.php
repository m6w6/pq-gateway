<?php

namespace pq\Gateway\Table;

use \pq\Gateway\Table;

/*
 * 	 case when att1.attname like '%\_'||att2.attname then
		substring(att1.attname from '^.*(?=_'||att2.attname||'$)')
	 else
		att1.attname
	 end
 */
const RELATION_SQL = <<<SQL
select
	regexp_replace(att1.attname, '_'||att2.attname||'$', '')
                  as "name"
	,cl1.relname  as "foreignTable"
	,att1.attname as "foreignColumn"
	,cl2.relname  as "referencedTable"
	,att2.attname as "referencedColumn"
from
     pg_constraint co
    ,pg_class      cl1
    ,pg_class      cl2
    ,pg_attribute  att1
    ,pg_attribute  att2
where
	 cl1.relname  = \$1
and co.confrelid != 0
and co.conrelid   = cl1.oid
and co.conkey[1]  = att1.attnum and cl1.oid = att1.attrelid
and co.confrelid  = cl2.oid
and co.confkey[1] = att2.attnum and cl2.oid = att2.attrelid
order by 
	att1.attnum
SQL;

/**
 * Foreign key list
 */
class Relations implements \Countable, \IteratorAggregate
{
	/**
	 * @var object
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
					$rel = $result->map([3,0], null, \pq\Result::FETCH_ASSOC);
					foreach ($rel as $table => $reference) {
						foreach ($reference as $name => $ref) {
							$this->references[$table][$name] = new Reference($ref);
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
	 * @return \RecursiveArrayIterator
	 */
	function getIterator() {
		return new \RecursiveArrayIterator($this->references);
	}
}
