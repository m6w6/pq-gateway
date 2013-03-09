<?php

namespace pq\Query;

/**
 * A synchronous query executor
 */
class Executor implements ExecutorInterface
{
	/**
	 * @var \pq\Connection
	 */
	protected $conn;
	
	/**
	 * Create a synchronous query executor
	 * @param \pq\Connection $conn
	 */
	function __construct(\pq\Connection $conn) {
		$this->conn = $conn;
	}
	
	/**
	 * @inheritdoc
	 * @return \pq\Connection
	 */
	function getConnection() {
		return $this->conn;
	}
	
	/**
	 * @inheritdoc
	 * @param \pq\Connection $conn
	 * @return \pq\Query\Executor
	 */
	function setConnection(\pq\Connection $conn) {
		$this->conn = $conn;
		return $this;
	}
	
	/**
	 * Execute the query synchronously through \pq\Connection::execParams()
	 * @param \pq\Query\WriterInterface $query
	 * @param callable $callback
	 * @return mixed
	 */
	function execute(WriterInterface $query, callable $callback) {
		return $callback($this->getConnection()->execParams($query, $query->getParams(), $query->getTypes()));
	}
}
