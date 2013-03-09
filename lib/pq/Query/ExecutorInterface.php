<?php

namespace pq\Query;

/**
 * An executor of \pq\Query\Writer queries
 */
interface ExecutorInterface
{
	/**
	 * Get the connection
	 * @return \pq\Connection
	 */
	function getConnection();
	
	/**
	 * Set the connection
	 * @param \pq\Connection $conn
	 * @return \pq\Query\ExecutorInterface
	 */
	function setConnection(\pq\Connection $conn);
	
	/**
	 * Execute the query and return the \pq\Result through $callback
	 * @param \pq\Query\WriterInterface $query
	 * @param callable $callback
	 * @return mixed the result of the callback
	 */
	function execute(WriterInterface $query, callable $callback);
}
