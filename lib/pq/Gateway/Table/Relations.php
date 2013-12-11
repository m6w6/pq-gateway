<?php

namespace pq\Gateway\Table;

use \pq\Gateway\Table;

const RELATION_SQL = <<<SQL
select
	 case att1.attname
	 when att2.attname
		then att1.attname
		else substring(att1.attname from '^.*(?=_'||att2.attname||'$)')
	 end          as "id"
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
	(	cl1.relname = \$1
	or	cl2.relname = \$1)
and co.confrelid != 0
and co.conrelid   = cl1.oid
and co.conkey[1]  = att1.attnum and cl1.oid = att1.attrelid
and co.confrelid  = cl2.oid
and co.confkey[1] = att2.attnum and cl2.oid = att2.attrelid
order by 
	 cl1.relname
	,att1.attnum
SQL;

/**
 * A foreighn key implementation
 */
class Relations
{
	/**
	 * @var array
	 */
	protected $references;
	
	function __construct(Table $table) {
		$cache = $table->getMetadataCache();
		if (!($this->references = $cache->get("$table#relations"))) {
			$table->getQueryExecutor()->execute(
				new \pq\Query\Writer(RELATION_SQL, array($table->getName())),
				function($result) use($table, $cache) {
					$this->references = $result->map(array(0,1), array(1,2,3,4), \pq\Result::FETCH_OBJECT);
					$cache->set("$table#relations", $this->references);
				}
			);
		}
	}
	
	function __isset($r) {
		return isset($this->references->$r);
	}
	
	function __get($r) {
		return $this->references->$r;
	}
	
	function __set($r, $v) {
		$this->references->$r = $v;
	}
	
	function __unset($r){
		unset($this->references->$r);
	}
}
