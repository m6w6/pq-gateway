<?php

namespace pq\Gateway;

use \pq\Query\Expr as QueryExpr;
use \pq\Query\Writer as QueryWriter;
use \pq\Query\Executor as QueryExecutor;

class Table implements \SplSubject
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
	 * @var \pq\Gateway\Table\Identity
	 */
	protected $identity;
	
	/**
	 * @var \pq\Gateway\Table\Attributes
	 */
	protected $attributes;
	
	/**
	 * @var \pq\Gateway\Table\Relations
	 */
	protected $relations;
	
	/**
	 * @var \pq\Gateway\Table\CacheInterface
	 */
	protected $metadataCache;
	
	/**
	 * @var \SplObjectStorage
	 */
	protected $observers;
	
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
	function __construct($name = null, \pq\Connection $conn = null) {
		if (isset($name)) {
			$this->name = $name;
		} elseif (!isset($this->name)) {
			throw new \InvalidArgumentException("Table must have a name");
		}
		$this->conn = $conn ?: static::$defaultConnection ?: new \pq\Connection;
		$this->observers = new \SplObjectStorage;
	}
	
	/**
	 * Get the complete PostgreSQL connection string
	 * @return string
	 */
	function __toString() {
		return (string) sprintf("postgresql://%s:%s@%s:%d/%s#%s",
			$this->conn->user,
			$this->conn->pass,
			$this->conn->host,
			$this->conn->port,
			$this->conn->db,
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
	 * Get the primary key
	 * @return \pq\Gateway\Table\Identity
	 */
	function getIdentity() {
		if (!isset($this->identity)) {
			$this->identity = new Table\Identity($this);
		}
		return $this->identity;
	}
	
	/**
	 * Get the table attribute definition (column list)
	 * @return \pq\Table\Attributes
	 */
	function getAttributes() {
		if (!isset($this->attributes)) {
			$this->attributes = new Table\Attributes($this);
		}
		return $this->attributes;
	}
	
	/**
	 * Get foreign key relations
	 * @return \pq\Gateway\Table\Relations
	 */
	function getRelations() {
		if (!isset($this->relations)) {
			$this->relations = new Table\Relations($this);
		}
		return $this->relations;
	}
	
	/**
	 * Get a foreign key relation
	 * @param string $table
	 * @param string $ref
	 * @return \pq\Gateway\Table\Reference
	 */
	function getRelation($table, $ref = null) {
		return $this->getRelations()->getReference($table, $ref);
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
	 * Attach an observer
	 * @param \SplObserver
	 * @return \pq\Gateway\Table
	 */
	function attach(\SplObserver $observer) {
		$this->observers->attach($observer);
		return $this;
	}
	
	/**
	 * Detach an observer
	 * @param \SplObserver
	 * @return \pq\Gateway\Table
	 */
	function detach(\SplObserver $observer) {
		$this->observers->attach($observer);
		return $this;
	}

	/**
	 * Implements \SplSubject
	 */
	function notify(\pq\Gateway\Row $row = null, $event = null, array &$where = null) {
		foreach ($this->observers as $observer) {
			$observer->update($this, $row, $event, $where);
		}
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
	 * @param string $lock
	 * @return mixed
	 */
	function find(array $where = null, $order = null, $limit = 0, $offset = 0, $lock = null) {
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
		if ($offset) {
			$query->write("OFFSET", $offset);
		}
		if ($lock) {
			$query->write("FOR", $lock);
		}
		return $this->execute($query);
	}
	
	/**
	 * Get the child rows of a row by foreign key
	 * @param \pq\Gateway\Row $foreign
	 * @param string $ref optional fkey name
	 * @param string $order
	 * @param int $limit
	 * @param int $offset
	 * @return mixed
	 */
	function of(Row $foreign, $ref = null, $order = null, $limit = 0, $offset = 0) {
		// select * from $this where $this->$foreignColumn = $foreign->$referencedColumn
		
		if (!($rel = $this->getRelation($foreign->getTable()->getName(), $ref))) {
			return $this->onResult(null);
		}
		
		$where = array();
		foreach ($rel as $key => $ref) {
			$where["$key="] = $foreign->$ref;
		}
		
		return $this->find($where, $order, $limit, $offset);
	}
	
	/**
	 * Get the parent rows of a row by foreign key
	 * @param \pq\Gateway\Row $foreign
	 * @param string $ref
	 * @return mixed
	 */
	function by(Row $foreign, $ref = null) {
		// select * from $this where $this->$referencedColumn = $me->$foreignColumn
		
		if (!($rel = $foreign->getTable()->getRelation($this->getName(), $ref))) {
			return $this->onResult(null);
		}
		
		$where = array();
		foreach ($rel as $key => $ref) {
			$where["$ref="] = $foreign->$key;
		}
		return $this->find($where);
	}
	
	/**
	 * Get rows dependent on other rows by foreign keys
	 * @param array $relations
	 * @param array $where
	 * @param string $order
	 * @param int $limit
	 * @param int $offset
	 * @return mixed
	 */
	function with(array $relations, array $where = null, $order = null, $limit = 0, $offset = 0) {
		$qthis = $this->conn->quoteName($this->getName());
		$query = $this->getQueryWriter()->reset();
		$query->write("SELECT", "$qthis.*", "FROM", $qthis);
		foreach ($relations as $relation) {
			if (!($relation instanceof Table\Reference)) {
				$relation = static::resolve($relation)->getRelation($this->getName());
			}
			if ($this->getName() === $relation->foreignTable) {
				$query->write("JOIN", $relation->referencedTable)->write("ON");
				foreach ($relation as $key => $ref) {
					$query->criteria(
						array(
							"{$relation->referencedTable}.{$ref}=" => 
								new QueryExpr("{$relation->foreignTable}.{$key}")
						)
					);
				}
			} else {
				$query->write("JOIN", $relation->foreignTable)->write("ON");
				foreach ($relation as $key => $ref) {
					$query->criteria(
						array(
							"{$relation->referencedTable}.{$ref}=" => 
								new QueryExpr("{$relation->foreignTable}.{$key}")
						)
					);
				}
			}
		}
		if ($where) {
			$query->write("WHERE")->criteria($where);
		}
		if ($order) {
			$query->write("ORDER BY", $order);
		}
		if ($limit) {
			$query->write("LIMIT", $limit);
		}
		if ($offset) {
			$query->write("OFFSET", $offset);
		}
		return $this->execute($query);
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
				$params[] = $query->param($val, $this->getAttributes()->getColumn($key)->type);
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
			$query->write($first ? "SET" : ",", $key, "=", 
				$query->param($val, $this->getAttributes()->getColumn($key)->type));
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
