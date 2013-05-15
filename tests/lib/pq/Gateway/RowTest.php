<?php

namespace pq\Gateway;

include_once __DIR__."/../../../setup.inc";

class RowTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \pq\Connection
	 */
	protected $conn;
	
	/**
	 * @var \pq\Gateway\Table
	 */
	protected $table;

	protected function setUp() {
		$this->conn = new \pq\Connection(PQ_TEST_DSN);
		$this->conn->exec(PQ_TEST_SETUP_SQL);
		Table::$defaultConnection = $this->conn;
		$this->table = new Table("test");
		$this->table->getQueryExecutor()->attach(new \QueryLogger());
	}

	protected function tearDown() {
		$this->conn->exec(PQ_TEST_TEARDOWN_SQL);
	}

	function testBasic() {
		$row = new Row($this->table, array("id" => 3), true);
		$this->assertTrue($row->isDirty());
		$row->refresh();
		$this->assertEquals(
			array(
				"id" => "3",
				"created" => new \pq\DateTime("tomorrow"),
				"counter" => "1",
				"number" => "1.1",
				"data" => "tomorrow",
				"list" => array(1,2,3),
				"prop" => null
			),
			$row->getData()
		);
		$this->assertFalse($row->isDirty());
	}
	
	function testGetTable() {
		$row = new Row($this->table);
		$this->assertSame($this->table, $row->getTable());
	}
	
	function testPessimisticLock() {
		$this->table->setLock(new Table\PessimisticLock);
		$txn = $this->table->getConnection()->startTransaction();
		$row = $this->table->find(null, null, 1)->current();
		$row->data = "foo";
		$row->update();
		$txn->commit();
		$this->assertSame("foo", $row->data->get());
	}
	
	function testPessimisticLockFail() {
		$this->table->setLock(new Table\PessimisticLock);
		$txn = $this->table->getConnection()->startTransaction();
		$row = $this->table->find(null, null, 1)->current();
		$row->data = "foo";
		executeInConcurrentTransaction(
			$this->table->getQueryExecutor(),
			"UPDATE {$this->table->getName()} SET data='bar' WHERE id=\$1", 
			array($row->id->get()));
		$this->setExpectedException("\\UnexpectedValueException", "Row has already been modified");
		$row->update();
		$txn->commit();
	}
	
	function testOptimisticLock() {
		$this->table->setLock(new Table\OptimisticLock("counter"));
		$row = $this->table->find(null, null, 1)->current();
		$cnt = $row->counter->get();
		$row->data = "foo";
		$row->update();
		$this->assertEquals("foo", $row->data->get());
		$this->assertEquals($cnt +1, $row->counter->get());
	}
	
	function testOptimisticLockFail() {
		$this->table->setLock(new Table\OptimisticLock("counter"));
		$row = $this->table->find(null, null, 1)->current();
		$row->data = "foo";
		executeInConcurrentTransaction(
			$this->table->getQueryExecutor(), 
			"UPDATE {$this->table->getName()} SET counter = 10 WHERE id=\$1", 
			array($row->id->get()));
		$this->setExpectedException("\\UnexpectedValueException", "No row updated");
		$row->update();
	}
	
	function testRef() {
		foreach ($this->table->find() as $row) {
			foreach ($row->reftest() as $ref) {
				$this->assertEquals($row->id->get(), $ref->test->current()->id->get());
			}
		}
	}
}
