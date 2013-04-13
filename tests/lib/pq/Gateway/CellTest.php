<?php

namespace pq\Gateway;

include __DIR__."/../../../setup.inc";

class CellTest extends \PHPUnit_Framework_TestCase {

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
		$this->table->getQueryExecutor()->attach(new \QueryLogger());
	}

	protected function tearDown() {
		$this->conn->exec(PQ_TEST_REFTABLE_DROP);
		$this->conn->exec(PQ_TEST_TABLE_DROP);
	}

	/**
	 * This is a very bad test…
	 */
	public function testBasic() {
		$row = $this->table->find(null, "id desc", 1)->current();
		foreach ($row->getData() as $key => $val) {
			$this->assertEquals($val, (string) $row->$key);
			$this->assertFalse($row->$key->isExpr());
			$this->assertFalse($row->$key->isDirty());
			$this->assertSame($val, $row->$key->get());
			$row->$key->mod(123);
			$this->assertNotEquals($val, (string) $row->$key);
			$this->assertTrue($row->$key->isExpr());
			$this->assertTrue($row->$key->isDirty());
			$this->assertNotSame($val, $row->$key->get());
			$this->assertEquals("$key + 123", (string) $row->$key->get());
			$row->$key->mod("foobar");
			$this->assertEquals("$key + 123 || 'foobar'", (string) $row->$key);
			$row->$key->mod(new \pq\Query\Expr(" - %s()", "now"));
			$this->assertEquals("$key + 123 || 'foobar' - now()", (string) $row->$key);
		}
	}
}
