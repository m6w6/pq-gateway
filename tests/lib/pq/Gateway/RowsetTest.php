<?php

namespace pq\Gateway;

include_once __DIR__."/../../../setup.inc";

class RowsetTest extends \PHPUnit_Framework_TestCase {

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

	public function test__invoke() {
		$rowset = $this->table->find();
		$this->table->setRowsetPrototype(null);
		$result = $this->table->find();
		$rowset2 = $rowset($result);
		$this->assertEquals($rowset, $rowset2);
	}

	public function testSetRowPrototype() {
		$prop = new \ReflectionProperty("\\pq\\Gateway\\Rowset", "row");
		$prop->setAccessible(true);
		$prototype = new Rowset($this->table);
		$this->assertEquals("\\pq\\Gateway\\Row", $prop->getValue($prototype));
		$prototype->setRowPrototype(null);
		$this->assertNull($prop->getValue($prototype));
		$this->table->setRowsetPrototype($prototype);
		$rowset = $this->table->find();
		foreach ($rowset as $row) {
			$this->assertInstanceOf("stdClass", $row);
			$this->assertObjectHasAttribute("id", $row);
		}
		$prototype->setRowPrototype(new Row($this->table));
		$rowset = $this->table->find();
		foreach ($rowset as $index => $row) {
			$this->assertInstanceOf("\\pq\\Gateway\\Row", $row);
			$this->assertEquals($index+1, $row->id->get());
		}
	}

	public function testGetTable() {
		$rowset = new Rowset($this->table);
		$this->assertSame($this->table, $rowset->getTable());
	}

	public function testCreate() {
		$rowset = new Rowset($this->table);
		$rowset->append(new Row($this->table));
		$rowset->create();
		$this->assertCount(1, $rowset);
		$this->assertCount(4, $this->table->find());
	}
	
	public function testCreateFail() {
		$this->setExpectedException("\\pq\\Exception");
		$rowset = new Rowset($this->table);
		$rowset->append(new Row($this->table, array("foo" => "bar"), true));
		$rowset->create();
	}

	public function testUpdate() {
		$rowset = $this->table->find();
		$rowset->apply(function($row) {
			$row->data = "updated";
		});
		$rowset->update();
		$rowset = $this->table->find();
		$rowset->apply(function($row) {
			$this->assertSame("updated", $row->data->get());
		});
	}

	public function testUpdateFail() {
		$this->setExpectedException("pq\\Exception");
		$rowset = $this->table->find();
		$rowset->apply(function($row) {
			$row->data = new \pq\Query\Expr("die");
		});
		$rowset->update();
		
	}

	public function testDelete() {
		$this->table->find()->delete();
		$this->assertCount(0, $this->table->find());
	}

	public function testDeleteFail() {
		$this->setExpectedException("Exception");
		$rowset = new Rowset($this->table);
		$rowset->append(new Row($this->table, array("xx" => 0)))->delete();
	}

	public function testJsonSerialize() {
		$json = sprintf('[{"id":"1","created":"%s","counter":"-1","number":"-1.1","data":"yesterday"}'
			.',{"id":"2","created":"%s","counter":"0","number":"0","data":"today"}'
			.',{"id":"3","created":"%s","counter":"1","number":"1.1","data":"tomorrow"}]',
			new \pq\DateTime("yesterday"),
			new \pq\DateTime("today"),
			new \pq\DateTime("tomorrow")
		);
		$this->assertJsonStringEqualsJsonString($json, json_encode($this->table->find()));
	}

	public function testIterator() {
		$counter = 0;
		foreach ($this->table->find() as $index => $row) {
			$this->assertSame($counter++, $index);
			$this->assertInstanceOf("\\pq\\Gateway\\Row", $row);
		}
	}

	public function testSeekEx() {
		$this->setExpectedException("\\OutOfBoundsException", "Invalid seek position (3)");
		$this->table->find()->seek(3);
	}
	
	public function testSeek() {
		$rowset = $this->table->find();
		for ($i = count($rowset); $i > 0; --$i) {
			$this->assertEquals($i, $rowset->seek($i-1)->current()->id->get());
		}
	}

	public function testCount() {
		$this->assertCount(3, $this->table->find());
	}

	public function testGetRows() {
		$rowset = $this->table->find();
		$rows = $rowset->getRows();
		$rowset2 = $rowset->filter(function($row) { return true; });
		$this->assertEquals($rows, $rowset2->getRows());
		$rowset3 = $rowset->filter(function($row) { return false; });
		$this->assertCount(0, $rowset3);
		$this->assertSame(array(), $rowset3->getRows());
		$this->assertCount(1, $rowset->filter(function($row) { return $row->id->get() == 1; }));
	}
}
