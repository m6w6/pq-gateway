<?php

namespace pq\Query;

require_once __DIR__."/../../../setup.inc";

use pq\Connection;
use React\Promise\Deferred as Reacted;
use Amp\Deferred as Amped;

class AsyncExecutorTest extends \PHPUnit_Framework_TestCase {
	private $conn;
	private $query;
	
	protected function setUp() {
		$this->conn = new Connection(PQ_TEST_DSN);
		$this->query = new Writer("SELECT \$1::int,\$2::int", [1,2]);
	}

	function testReact() {
		$exec = new AsyncExecutor($this->conn);
		$exec->setCallbacks(
		# init context
		function() {
			return new Reacted;
		},
		# done
		function(Reacted $context, $result) {
			$context->resolve($result);
		},
		# then
		function(Reacted $context, callable $cb) {
			return $context->promise()->then($cb);
		});

		$guard = new \stdClass;
		$exec->execute($this->query, function($result) use($guard) {
			$guard->result = $result;
		});
		$this->conn->getResult();
		$this->assertTrue(!empty($guard->result), "guard is empty");
		$this->assertInstanceOf("pq\\Result", $guard->result);
		$this->assertSame([[1,2]], $guard->result->fetchAll());
	}

	function testAmp() {
		$exec = new AsyncExecutor($this->conn);
		$exec->setCallbacks(
		# init context
		function() {
			return new Amped;
		},
		# done
		function(Amped $context, $result) {
			$context->succeed($result);
		},
		# then
		function(Amped $context, callable $cb) {
			return $context->promise()->when(function($error, $result) use ($cb) {
				$cb($result);
			});
		});
		$guard = new \stdClass;
		$exec->execute($this->query, function($result) use($guard) {
			$guard->result = $result;
		});
		$this->conn->getResult();
		$this->assertTrue(!empty($guard->result), "guard is empty");
		$this->assertInstanceOf("pq\\Result", $guard->result);
		$this->assertSame([[1,2]], $guard->result->fetchAll());
	}
}
