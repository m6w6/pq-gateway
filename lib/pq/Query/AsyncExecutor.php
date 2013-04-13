<?php

namespace pq\Query\Executor;

use \pq\Query\Executor;
use \pq\Query\WriterInterface;

/**
 * @requires \React\Promise
 */
use \React\Promise\Deferred;

/**
 * An asynchronous query executor
 */
class Async extends Executor
{
	/**
	 * Execute the query asynchronously through \pq\Connection::execParamsAsync()
	 * @param \pq\Query\WriterInterface $query
	 * @param callable $callback
	 * @return \React\Promise\DeferredPromise
	 */
	function execute(WriterInterface $query, callable $callback) {
		$this->result = null;
		$this->query = $query;
		$this->notify();
		
		$deferred = new Deferred;
		$this->getConnection()->execParamsAsync($query, $query->getParams(), $query->getTypes(), 
			array($deferred->resolver(), "resolve"));
		
		return $deferred->then(function($result) {
			$this->result = $result;
			$this->notify();
		})->then($callback);
	}
}
