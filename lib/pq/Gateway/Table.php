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
	 * @var array
	 */
	protected $dependents;
	
	/**
	 * @param string $name
	 * @param \pq\Connection $conn
	 * @param array $dependents
	 */
	function __construct($name, \pq\Connection $conn = null, array $dependents = array()) {
		$this->name = $name;
		$this->conn = $conn ?: static::$defaultConnection ?: new \pq\Connection;
		$this->dependents = $dependents;
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
	public function onResult(\pq\Result $result) {
		if ($result->status != \pq\Result::TUPLES_OK) {
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
	 * Get the parent row of a row by foreign key
	 * @param \pq\Gateway\Row $dependent
	 * @param string $name optional fkey name
	 * @param string $order
	 * @param int $limit
	 * @param int $offset
	 * @return mixed
	 */
	function of(Row $dependent, $name = null, $order = null, $limit = 0, $offset = 0) {
		if (!$name) {
			$name = $dependent->getTable()->getName();
		}
		return $this->find(array("{$name}_id=" => $dependent->id),
			$order, $limit, $offset);
	}
	
	/**
	 * Get the child rows of a row by foreign key
	 * @param \pq\Gateway\Row $me
	 * @param string $dependent
	 * @param string $order
	 * @param int $limit
	 * @param int $offset
	 * @return mixed
	 * @throws \LogicException
	 */
	function by(Row $me, $dependent, $order = null, $limit = 0, $offset = 0) {
		if (!isset($this->dependents[$dependent])) {
			throw new \LogicException("Unknown dependent table $dependent");
		}
		
		$dependentClass = $this->dependents[$dependent];
		$dependentModel = new $dependentClass($this->conn);
		return $dependentModel->of($me, null, $order, $limit, $offset);
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
