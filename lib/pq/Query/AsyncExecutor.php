<?php

namespace pq\Query;

use \pq\Query\Executor;
use \pq\Query\WriterInterface;

/**
 * An asynchronous query executor
 */
class AsyncExecutor extends Executor
{
	/**
	 * Context initializer
	 * @var callable
	 */
	protected $init;
	
	/**
	 * Result resolver
	 * @var callable
	 */
	protected $done;
	
	/**
	 * Callback queue
	 * @var callable
	 */
	protected $then;
	
	/**
	 * Set (promise) callbacks
	 * 
	 * Example with reactphp:
	 * <code>
	 * use React\Promise\Deferred;
	 * 
	 * $exec = new pq\Query\AsyncExecutor(new pq\Connection);
	 * $exec->setCallbacks(
	 * # init context
	 * function() {
	 *		return new Deferred;
	 * },
	 * # done
	 * function(Deferred $context, $result) {
	 *		$context->resolve($result);
	 * },
	 * # then
	 * function(Deferred $context, callable $cb) {
	 *		return $context->promise()->then($cb);
	 * });
	 * $exec->execute($queryWriter, function($result){});
	 * </code>
	 * 
	 * Example with amphp:
	 * <code>
	 * use Amp\Deferred;
	 * 
	 * $exec = new pq\Query\AsyncExecutor(new pq\Connection);
	 * $exec->setCallbacks(
	 * # init context
	 * function() {
	 *		return new Deferred;
	 * },
	 * # done
	 * function(Deferred $context, $result) {
	 *		$context->succeed($result);
	 * },
	 * # then
	 * function(Deferred $context, callable $cb) {
	 *		return $context->promise()->when(function($error, $result) use ($cb) {
	 *			$cb($result);
	 *		});
	 * });
	 * $exec->execute($queryWriter, function($result){});
	 * </code>
	 * 
	 * @param callable $init context initializer as function()
	 * @param callable $done result receiver as function($context, $result)
	 * @param callable $then callback queue as function($context, $callback)
	 */
	function setCallbacks(callable $init, callable $done, callable $then) {
		$this->init = $init;
		$this->done = $done;
		$this->then = $then;
	}
	
	/**
	 * Get (promise) callbacks previously set
	 * @return array(callable)
	 */
	function getCallbacks() {
		return array($this->init, $this->done, $this->then);
	}
	
	/**
	 * Prepare (promise) callbacks
	 * @param callable $callback
	 * @return array($context, $resolver)
	 */
	protected function prepareCallbacks(callable $callback/*, ... */) {
		list($init, $done, $then) = $this->getCallbacks();
		
		$context = $init();
		foreach (func_get_args() as $cb) {
			$then($context, $cb);
		}
		
		return array($context, function($result) use ($context, $done) {
			$done($context, $result);
		});
	}
	
	/**
	 * Execute the query asynchronously through \pq\Connection::execParamsAsync()
	 * @param \pq\Query\WriterInterface $query
	 * @param callable $callback result callback
	 * @return mixed context created by the init callback
	 */
	function execute(WriterInterface $query, callable $callback) {
		$this->result = null;
		$this->query = $query;
		$this->notify();
		
		list($context, $resolver) = $this->prepareCallbacks(
			function(\pq\Result $result) {
				$this->result = $result;
				$this->notify();
				return $result;
			}, $callback);
		$this->getConnection()->execParamsAsync($query, $query->getParams(), 
			$query->getTypes(), $resolver);
		return $context;
	}
}
