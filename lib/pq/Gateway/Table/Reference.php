<?php

namespace pq\Gateway\Table;

/**
 * Foreign key
 */
class Reference implements \IteratorAggregate
{
	/**
	 * @var string
	 */
	public $name;
	
	/**
	 * @var string
	 */
	public $foreignTable;
	
	/**
	 * @var array
	 */
	public $foreignColumns;
	
	/**
	 * @var string
	 */
	public $referencedTable;
	
	/**
	 * @var array
	 */
	public $referencedColumns;
	
	/**
	 * @param array $ref
	 */
	function __construct($ref) {
		$this->name = self::name($ref["foreignColumns"], $ref["referencedColumns"]);
		$this->foreignTable = $ref["foreignTable"];
		$this->foreignColumns = $ref["foreignColumns"];
		$this->referencedTable = $ref["referencedTable"];
		$this->referencedColumns = $ref["referencedColumns"];
	}
	
	/**
	 * @param array $state
	 * @return \pq\Gateway\Table\Reference
	 */
	static function __set_state($state) {
		return new static($state);
	}
	
	/**
	 * Compose an identifying name
	 * @param array $foreignColumns
	 * @param array $referencedColumns
	 * @return string
	 */
	static function name(array $foreignColumns, array $referencedColumns) {
		return implode("_", array_map(function($ck, $cr) {
			return preg_replace("/_$cr\$/", "", $ck);
		}, $foreignColumns, $referencedColumns));
	}
	
	/**
	 * Implements IteratorAggregate
	 * @return \ArrayIterator
	 */
	function getIterator() {
		return new \ArrayIterator(array_combine(
			$this->foreignColumns, $this->referencedColumns));
	}
}
