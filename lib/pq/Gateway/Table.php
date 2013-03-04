<?php

namespace pq\Gateway;

use \pq\Query\Writer as QueryWriter;

class Table
{
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
	protected $rowset;

	/**
	 * @param \pq\Connection $conn
	 * @param string $name
	 */
	function __construct(\pq\Connection $conn, $name, $rowset = "\\pq\\Gateway\\Rowset") {
		$this->conn   = $conn;
		$this->name   = $name;
		$this->rowset = $rowset;
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
	 * @param \pq\Query\Writer $query
	 * @return mixed
	 */
	protected function execute(QueryWriter $query) {
		$result = $query->exec($this->conn);
		
		if ($result->status != \pq\Result::TUPLES_OK) {
			return $result;
		}
		
		if (is_callable($this->rowset)) {
			return call_user_func($this->rowset, $result);
		}
		
		if ($this->rowset) {
			$rowset = $this->rowset;
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
	 * @return \pq\Result
	 */
	function find(array $where = null, $order = null, $limit = 0, $offset = 0) {
		$query = new QueryWriter("SELECT * FROM ". $this->conn->quoteName($this->name));
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
	 * Insert a row into the table
	 * @param array $data
	 * @param string $returning
	 * @return \pq\Result
	 */
	function create(array $data, $returning = "*") {
		$params = array();
		$query = new QueryWriter("INSERT INTO ".$this->conn->quoteName($this->name)." (");
		foreach ($data as $key => $val) {
			$query->write($key);
			$params[] = $query->param($val);
		}
		$query->write(") VALUES (", $params, ")");
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
	 * @retunr \pq\Result
	 */
	function update(array $where, array $data, $returning = "*") {
		$query = new QueryWriter("UPDATE ".$this->conn->quoteName($this->name)." SET");
		foreach ($data as $key => $val) {
			$query->write($key, "=", $query->param($val));
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
	 * @return pq\Result
	 */
	function delete(array $where, $returning = null) {
		$query = new QueryWriter("DELETE FROM ".$this->conn->quoteName($this->name));
		$query->write("WHERE")->criteria($where);
		if (strlen($returning)) {
			$query->write("RETURNING", $returning);
		}
		return $this->execute($query);
	}
}
