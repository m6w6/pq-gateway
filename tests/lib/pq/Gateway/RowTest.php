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
		$this->conn->exec(PQ_TEST_TABLE_CREATE);
		$this->conn->exec(PQ_TEST_REFTABLE_CREATE);
		$this->conn->exec(PQ_TEST_DATA);
		Table::$defaultConnection = $this->conn;
		$this->table = new Table("test");
	}

	protected function tearDown() {
		$this->conn->exec(PQ_TEST_REFTABLE_DROP);
		$this->conn->exec(PQ_TEST_TABLE_DROP);
	}

	function testBasic() {
		$row = new Row($this->table, array("id" => 3), true);
		$this->assertTrue($row->isDirty());
		$row->refresh();
		$this->assertSame(
			array(
				"id" => "3",
				"created" => date("Y-m-d H:i:s", strtotime("tomorrow")),
				"counter" => "1",
				"number" => "1.1",
				"data" => "tomorrow"
			),
			$row->getData()
		);
		$this->assertFalse($row->isDirty());
	}
	
	function testGetTable() {
		$row = new Row($this->table);
		$this->assertSame($this->table, $row->getTable());
	}
}
