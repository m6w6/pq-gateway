<?php

namespace pq\Query\Executor;

use \pq\Query\ExecutorInterface;
use \pq\Query\WriterInterface;

use \React\Promise\Deferred;

/**
 * An asynchronous query executor
 */
class Async implements ExecutorInterface
{
	protected $conn;
	
	/**
	 * Create a asynchronous query exectuor
	 * @param \pq\Connection $conn
	 */
	function __construct(\pq\Connection $conn) {
		$this->conn = $conn;
	}
	
	/**
	 * Get the connection
	 * @return \pq\Connection
	 */
	function getConnection() {
		return $this->conn;
	}
	
	/**
	 * Set the connection
	 * @param \pq\Connection $conn
	 * @return \pq\Query\Executor\Async
	 */
	function setConnection(\pq\Connection $conn) {
		$this->conn = $conn;
		return $this;
	}
	
	/**
	 * Execute the query asynchronously through \pq\Connection::execParamsAsync()
	 * @param \pq\Query\WriterInterface $query
	 * @param callable $callback
	 * @return \React\Promise\DeferredPromise
	 */
	function execute(WriterInterface $query, callable $callback) {
		$deferred = new Deferred; // FIXME
		$this->getConnection()->execParamsAsync($query, $query->getParams(), $query->getTypes(), 
			array($deferred->resolver(), "resolve"));
		return $deferred->then($callback);
	}
}
