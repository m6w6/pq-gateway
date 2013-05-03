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
		$this->conn->exec(PQ_TEST_SETUP_SQL);
		Table::$defaultConnection = $this->conn;
		$this->table = new Table("test");
		$this->table->getQueryExecutor()->attach(new \QueryLogger());
	}

	protected function tearDown() {
		$this->conn->exec(PQ_TEST_TEARDOWN_SQL);
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
	
	public function testRef() {
		$rows = $this->table->find(null, "id desc", 2);
		$reft = new Table("reftest");
		$refs = new Rowset($reft);
		$refs->append($rows->seek(0)->current()->reftest()->current());
		$refs->append($rows->seek(1)->current()->reftest()->current());
		$refs->seek(0)->current()->test = $rows->seek(1)->current();
		$refs->seek(1)->current()->test = $rows->seek(0)->current();
		$refs->update();
	}
}
