<?php

namespace pq\Gateway;

include_once __DIR__."/../../../setup.inc";

class TableTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \pq\Connection
	 */
	protected $conn;
	
	/**
	 * @var Table
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
	
	public function testSetRowsetPrototype() {
		$prop = new \ReflectionProperty("\\pq\\Gateway\\Table", "rowset");
		$prop->setAccessible(true);
		$this->assertEquals("\\pq\\Gateway\\Rowset", $prop->getValue($this->table));
		$this->table->setRowsetPrototype(null);
		$this->assertNull($prop->getValue($this->table));
		$rowset = new \pq\Gateway\Rowset($this->table);
		$this->table->setRowsetPrototype($rowset);
		$this->assertSame($rowset, $prop->getValue($this->table));
	}

	public function testGetConnection() {
		$this->assertSame($this->conn, $this->table->getConnection());
	}

	public function testGetName() {
		$this->assertSame("test", $this->table->getName());
	}

	public function testFind() {
		$rowset = $this->table->find();
		$this->assertInstanceOf("\\pq\\Gateway\\Rowset", $rowset);
		$rowset = $this->table->find(array("id = " => 1));
		$this->assertInstanceOf("\\pq\\Gateway\\Rowset", $rowset);
		$rowset = $this->table->find(array("id = " => 0));
		$this->assertInstanceOf("\\pq\\Gateway\\Rowset", $rowset);
		$rowset = $this->table->find(array(array("id<" => 2), array("id>" => 2)));
		$this->assertInstanceOf("\\pq\\Gateway\\Rowset", $rowset);
	}

	public function testCreate() {
		$rowset = $this->table->create(array("id" => new \pq\Query\Expr("DEFAULT")));
		$this->assertInstanceOf("\\pq\\Gateway\\Rowset", $rowset);
		$this->assertCount(1, $rowset);
	}

	public function testUpdate() {
		$row = $this->table->create(array())->current();
		$data = array(
			"created" => new \pq\DateTime("2013-03-03 03:03:03"),
			"counter" => 2,
			"number" => 2.2,
			"data" => "this is a test",
			"list" => array(3,2,1),
			"prop" => null
		);
		$row = $this->table->update(array("id = " => $row->id), $data)->current();
		$data = array("id" => $row->id->get()) + $data;
		$this->assertEquals($data, $row->getData());
	}

	public function testDelete() {
		$this->table->delete(array("id!=" => 0));
		$this->assertCount(0, $this->table->find());
	}
	
	public function testWith() {
		$rowset = $this->table->with(["reftest"], array("another_test_id=" => 2));
		$this->assertCount(1, $rowset);
		$this->assertEquals(array(
			"id" => 2,
			"created" => new \pq\DateTime("today"),
			"counter" => 0,
			"number" => 0,
			"data" => "today",
			"list" => array(0,1,2),
			"prop" => null
		), $rowset->current()->getData());
	}
}
