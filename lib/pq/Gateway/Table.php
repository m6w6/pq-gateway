<?php

namespace pq\Gateway;

use \pq\Query\Writer as QueryWriter;
use \pq\Query\Executor as QueryExecutor;

class Table
{
	/**
	 * @var \pq\Connection
	 */
	public static $defaultConnection;
	
	/**
	 * @var callable
	 */
	public static $defaultResolver;
	
	/**
	 * @var \pq\Gateway\Table\CacheInterface
	 */
	public static $defaultMetadataCache;
	
	/**
	 * @var \pq\Connection
	 */
	protected $conn;

	/**
	 * @var string
	 */
	protected $name;
	
	/**
	 * @var string
	 */
	protected $rowset = "\\pq\\Gateway\\Rowset";
	
	/**
	 * @var \pq\Query\WriterIterface
	 */
	protected $query;
	
	/**
	 * @var \pq\Query\ExecutorInterface
	 */
	protected $exec;
	
	/**
	 * @var \pq\Gateway\Table\Relations
	 */
	protected $relations;
	
	/**
	 * @var \pq\Gateway\Table\CacheInterface
	 */
	protected $metadataCache;
	
	/**
	 * @param string $table
	 * @return \pq\Gateway\Table
	 */
	public static function resolve($table) {
		if ($table instanceof Table) {
			return $table;
		}
		if (is_callable(static::$defaultResolver)) {
			if (($resolved = call_user_func(static::$defaultResolver, $table))) {
				return $resolved;
			}
		}
		return new Table($table);
	}

	/**
	 * @param string $name
	 * @param \pq\Connection $conn
	 * @param array $dependents
	 */
	function __construct($name, \pq\Connection $conn = null) {
		$this->name = $name;
		$this->conn = $conn ?: static::$defaultConnection ?: new \pq\Connection;
	}
	
	/**
	 * Get the complete PostgreSQL connection string
	 * @return string
	 */
	function __toString() {
		return sprintf("postgresql://%s:%s@%s:%d/%s?%s#%s",
			$this->conn->user,
			$this->conn->pass,
			$this->conn->host,
			$this->conn->port,
			$this->conn->db,
			$this->conn->options,
			$this->getName()
		);
	}
	
	/**
	 * Set the rowset prototype
	 * @param mixed $rowset
	 * @return \pq\Gateway\Table
	 */
	function setRowsetPrototype($rowset) {
		$this->rowset = $rowset;
		return $this;
	}
	
	/**
	 * Get the rowset prototype
	 * @return mixed
	 */
	function getRowsetPrototype() {
		return $this->rowset;
	}
	
	/**
	 * Set the query writer
	 * @param \pq\Query\WriterInterface $query
	 * @return \pq\Gateway\Table
	 */
	function setQueryWriter(\pq\Query\WriterInterface $query) {
		$this->query = $query;
		return $this;
	}
	
	/**
	 * Get the query writer
	 * @return \pq\Query\WriterInterface
	 */
	function getQueryWriter() {
		if (!$this->query) {
			$this->query = new QueryWriter;
		}
		return $this->query;
	}
	
	/**
	 * Set the query executor
	 * @param \pq\Query\ExecutorInterface $exec
	 * @return \pq\Gateway\Table
	 */
	function setQueryExecutor(\pq\Query\ExecutorInterface $exec) {
		$this->exec = $exec;
		return $this;
	}
	
	/**
	 * Get the query executor
	 * @return \pq\Query\ExecutorInterface
	 */
	function getQueryExecutor() {
		if (!$this->exec) {
			$this->exec = new QueryExecutor($this->conn);
		}
		return $this->exec;
	}
	
	/**
	 * Get the metadata cache
	 * @return \pq\Gateway\Table\CacheInterface
	 */
	function getMetadataCache() {
		if (!isset($this->metadatCache)) {
			$this->metadataCache = static::$defaultMetadataCache ?: new Table\StaticCache;
		}
		return $this->metadataCache;
	}
	
	/**
	 * Set the metadata cache
	 * @param \pq\Gateway\Table\CacheInterface $cache
	 */
	function setMetadataCache(Table\CacheInterface $cache) {
		$this->metadataCache = $cache;
		return $this;
	}
	
	/**
	 * Get foreign key relations
	 * @param string $to fkey
	 * @return \pq\Gateway\Table\Relations|stdClass
	 */
	function getRelations($to = null) {
		if (!isset($this->relations)) {
			$this->relations = new Table\Relations($this);
		}
		if (isset($to)) {
			if (!isset($this->relations->$to)) {
				return null;
			}
			return $this->relations->$to;
		}
		return $this->relations;
	}
	
	/**
	 * Check whether a certain relation exists
	 * @param string $name
	 * @param string $table
	 * @return bool
	 */
	function hasRelation($name, $table = null) {
		if (!($rel = $this->getRelations($name))) {
			return false;
		}
		if (!isset($table)) {
			return true;
		}
		return isset($rel->$table);
	}
	
	/**
	 * @return \pq\Connection
	 */
	function getConnection() {
		return $this->conn;
	}
	
	/**
	 * @return string
	 */
	function getName() {
		return $this->name;
	}

	/**
	 * Execute the query
	 * @param \pq\Query\WriterInterface $query
	 * @return mixed
	 */
	protected function execute(QueryWriter $query) {
		return $this->getQueryExecutor()->execute($query, array($this, "onResult"));
	}

	/**
	 * Retreives the result of an executed query
	 * @param \pq\Result $result
	 * @return mixed
	 */
	public function onResult(\pq\Result $result = null) {
		if ($result && $result->status != \pq\Result::TUPLES_OK) {
			return $result;
		}
		
		$rowset = $this->getRowsetPrototype();
		if (is_callable($rowset)) {
			return $rowset($result);
		} elseif ($rowset) {
			return new $rowset($this, $result);
		}
		
		return $result;
	}
	
	/**
	 * Find rows in the table
	 * @param array $where
	 * @param array|string $order
	 * @param int $limit
	 * @param int $offset
	 * @return mixed
	 */
	function find(array $where = null, $order = null, $limit = 0, $offset = 0) {
		$query = $this->getQueryWriter()->reset();
		$query->write("SELECT * FROM", $this->conn->quoteName($this->name));
		if ($where) {
			$query->write("WHERE")->criteria($where);
		}
		if ($order) {
			$query->write("ORDER BY", $order);
		}
		if ($limit) {
			$query->write("LIMIT", $limit);
		}
		$query->write("OFFSET", $offset);
		return $this->execute($query);
	}
	
	/**
	 * Get the child rows of a row by foreign key
	 * @param \pq\Gateway\Row $foreign
	 * @param string $name optional fkey name
	 * @param string $order
	 * @param int $limit
	 * @param int $offset
	 * @return mixed
	 */
	function of(Row $foreign, $name = null, $order = null, $limit = 0, $offset = 0) {
		// select * from $this where $this->$foreignColumn = $foreign->$referencedColumn
		
		if (!isset($name)) {
			$name = $this->getName();
		}
		
		if (!$foreign->getTable()->hasRelation($name, $this->getName())) {
			return $this->onResult(null);
		}
		$rel = $foreign->getTable()->getRelations($name)->{$this->getName()};
		
		return $this->find(
			array($rel->foreignColumn . "=" => $foreign->{$rel->referencedColumn}),
			$order, $limit, $offset
		);
	}
	
	/**
	 * Get the parent rows of a row by foreign key
	 * @param \pq\Gateway\Row $me
	 * @param string $foreign
	 * @param string $order
	 * @param int $limit
	 * @param int $offset
	 * @return mixed
	 */
	function by(Row $me, $foreign, $order = null, $limit = 0, $offset = 0) {
		// select * from $foreign where $foreign->$referencedColumn = $me->$foreignColumn
		
		if (!$this->hasRelation($foreign, $this->getName())) {
			return $this->onResult(null);
		}
		$rel = $this->getRelations($foreign)->{$this->getName()};
		
		return static::resolve($rel->referencedTable)->find(
			array($rel->referencedColumn . "=" => $me->{$rel->foreignColumn}),
			$order, $limit, $offset
		);
	}

	/**
	 * Insert a row into the table
	 * @param array $data
	 * @param string $returning
	 * @return mixed
	 */
	function create(array $data = null, $returning = "*") {
		$query = $this->getQueryWriter()->reset();
		$query->write("INSERT INTO", $this->conn->quoteName($this->name));
		if ($data) {
			$first = true;
			$params = array();
			foreach ($data as $key => $val) {
				$query->write($first ? "(" : ",", $key);
				$params[] = $query->param($val);
				$first and $first = false;
			}
			$query->write(") VALUES (", $params, ")");
		} else {
			$query->write("DEFAULT VALUES");
		}
		
		if (strlen($returning)) {
			$query->write("RETURNING", $returning);
		}
		return $this->execute($query);
	}

	/**
	 * Update rows in the table
	 * @param array $where
	 * @param array $data
	 * @param string $returning
	 * @retunr mixed
	 */
	function update(array $where, array $data, $returning = "*") {
		$query = $this->getQueryWriter()->reset();
		$query->write("UPDATE", $this->conn->quoteName($this->name));
		$first = true;
		foreach ($data as $key => $val) {
			$query->write($first ? "SET" : ",", $key, "=", $query->param($val));
			$first and $first = false;
		}
		$query->write("WHERE")->criteria($where);
		if (strlen($returning)) {
			$query->write("RETURNING", $returning);
		}
		return $this->execute($query);
	}

	/**
	 * Delete rows from the table
	 * @param array $where
	 * @param string $returning
	 * @return mixed
	 */
	function delete(array $where, $returning = null) {
		$query = $this->getQueryWriter()->reset();
		$query->write("DELETE FROM", $this->conn->quoteName($this->name));
		$query->write("WHERE")->criteria($where);
		if (strlen($returning)) {
			$query->write("RETURNING", $returning);
		}
		return $this->execute($query);
	}
}
