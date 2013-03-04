<?php

namespace pq\Gateway;

use \pq\Query\Writer as QueryWriter;

class Table
{
	/**
	 * @var \pq\Connection
	 */
	protected $connection;

	/**
	 * @var string
	 */
	protected $name;
	
	/**
	 * @var string
	 */
	protected $rowset;

	/**
	 * @param \pq\Connection $c
	 * @param string $name
	 */
	function __construct(\pq\Connection $c, $name, $rowset = "\\pq\\Gateway\\Rowset") {
		$this->connection = $c;
		$this->name = $name;
		$this->rowset = $rowset;
	}
	
	/**
	 * Accessor to read-only properties
	 * @param string $p
	 */
	function __get($p) {
		return $this->$p;
	}

	/**
	 * @param \pq\Query\Writer $query
	 * @param array $criteria
	 * @param string $join
	 */
	protected function criteria(QueryWriter $query, array $criteria, $join = "AND") {
		$joinable = false;
		$query->write("(");
		foreach ($criteria as $lop => $rop) {
			if (is_array($rop)) {
				if ($joinable) {
					$query->write(")", $join, "(");
				}
				$this->criteria($query, $rop, is_int($lop) ? "AND" : $lop);
			} else {
				if ($joinable) {
					$query->write(")", $join, "(");
				}
				if (!is_int($lop)) {
					$query->write($lop);
				}
				$query->write($query->param($rop));
			}
			$joinable or $joinable = true;
		}
		$query->write(")");
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
		$query = new QueryWriter("SELECT * FROM ". $this->connection->quoteName($this->name));
		if ($where) {
			$this->criteria($query->write("WHERE"), $where);
		}
		if ($order) {
			$query->write("ORDER BY", $order);
		}
		if ($limit) {
			$query->write("LIMIT", $limit);
		}
		$query->write("OFFSET", $offset);
		return new Rowset($this, $query->exec($this->connection));
	}

	/**
	 * Insert a row into the table
	 * @param array $data
	 * @param string $returning
	 * @return \pq\Result
	 */
	function create(array $data, $returning = "*") {
		$params = array();
		$query = new QueryWriter("INSERT INTO ".$this->connection->quoteName($this->name)." (");
		foreach ($data as $key => $val) {
			$query->write($key);
			$params[] = $query->param($val);
		}
		$query->write(") VALUES (", $params, ")");
		if (strlen($returning)) {
			$query->write("RETURNING", $returning);
		}
		$result = $query->exec($this->connection);
		if ($result->status == \pq\Result::TUPLES_OK) {
			$rowset = $this->rowset;
			return new $rowset($this, $result);
		}
		return $result;
	}

	/**
	 * Update rows in the table
	 * @param array $where
	 * @param array $data
	 * @param string $returning
	 * @retunr \pq\Result
	 */
	function update(array $where, array $data, $returning = "*") {
		$query = new QueryWriter("UPDATE ".$this->connection->quoteName($this->name)." SET");
		foreach ($data as $key => $val) {
			$query->write($key, "=", $query->param($val));
		}
		$this->criteria($query->write("WHERE"), $where);
		if (strlen($returning)) {
			$query->write("RETURNING", $returning);
		}
		$result = $query->exec($this->connection);
		if ($result->status == \pq\Result::TUPLES_OK) {
			$rowset = $this->rowset;
			return new $rowset($this, $result);
		}
		return $result;
	}

	/**
	 * Delete rows from the table
	 * @param array $where
	 * @param string $returning
	 * @return pq\Result
	 */
	function delete(array $where, $returning = null) {
		$query = new QueryWriter("DELETE FROM ".$this->connection->quoteName($this->name));
		$this->criteria($query->write("WHERE"), $where);
		if (strlen($returning)) {
			$query->write("RETURNING", $returning);
		}
		$result = $query->exec($this->connection);
		if ($result->status == \pq\Result::TUPLES_OK) {
			$rowset = $this->rowset;
			return new $rowset($this, $result);
		}
		return $result;
	}
}

