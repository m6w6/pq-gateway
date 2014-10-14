<?php

namespace pq\Gateway\Table;

/**
 * Foreign key
 */
class Reference
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
	 * @var string
	 */
	public $foreignColumn;
	
	/**
	 * @var string
	 */
	public $referencedTable;
	
	/**
	 * @var string
	 */
	public $referencedColumn;
	
	/**
	 * @param array $state
	 */
	function __construct($state) {
		$this->name = $state["name"];
		$this->foreignColumn = $state["foreignColumn"];
		$this->foreignTable = $state["foreignTable"];
		$this->referencedColumn = $state["referencedColumn"];
		$this->referencedTable = $state["referencedTable"];
	}
	
	/**
	 * @param array $state
	 * @return \pq\Gateway\Table\Reference
	 */
	static function __set_state($state) {
		return new static($state);
	}
}
