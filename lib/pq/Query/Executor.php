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
	 * @var \SplObjectStorage
	 */
	protected $observers;
	
	/**
	 * @var \pq\Query\WriterInterface
	 */
	protected $query;
	
	/**
	 * @var \pq\Result
	 */
	protected $result;
	
	/**
	 * Create a synchronous query executor
	 * @param \pq\Connection $conn
	 */
	function __construct(\pq\Connection $conn) {
		$this->conn = $conn;
		$this->observers = new \SplObjectStorage;
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
	 * @inheritdoc
	 * @return WriterInterface
	 */
	function getQuery() {
		return $this->query;
	}
	
	/**
	 * @inheritdoc
	 * @return \pq\Result
	 */
	function getResult() {
		return $this->result;
	}
	
	/**
	 * Execute the query synchronously through \pq\Connection::execParams()
	 * @param \pq\Query\WriterInterface $query
	 * @param callable $callback
	 * @return mixed
	 */
	function execute(WriterInterface $query, callable $callback) {
		$this->result = null;
		$this->query = $query;
		$this->notify();
		$this->result = $this->getConnection()->execParams($query, $query->getParams(), $query->getTypes());
		$this->notify();
		return $callback($this->result);
	}
	
	/**
	 * @implements \SplSubject
	 * @param \SplObserver $observer
	 */
	function attach(\SplObserver $observer) {
		$this->observers->attach($observer);
	}
	
	/**
	 * @implements \SplSubject
	 * @param \SplObserver $observer
	 */
	function detach(\SplObserver $observer) {
		$this->observers->detach($observer);
	}
	
	/**
	 * @implements \SplSubject
	 */
	function notify() {
		foreach ($this->observers as $observer){
			$observer->update($this);
		}
	}
}
