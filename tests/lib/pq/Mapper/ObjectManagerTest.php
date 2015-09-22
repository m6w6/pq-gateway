<?php

namespace pq\Mapper;

require_once __DIR__."/../../../setup.inc";

use BadMethodCallException;
use pq\Connection;
use pq\Gateway\Row;
use pq\Gateway\Table;
use pq\Mapper\ObjectManager;
use QueryLogger;
use TestModel;

class ObjectManagerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Connection
	 */
	protected $conn;

	/**
	 * @var Mapper
	 */
	protected $mapper;

	/**
	 * @var ObjectManager
	 */
	protected $objectManager;

	protected function setUp() {
		$this->conn = new Connection(PQ_TEST_DSN);
		$this->conn->exec(PQ_TEST_SETUP_SQL);
		Table::$defaultConnection = $this->conn;
		$this->mapper = new Mapper;
		$mapping = TestModel::mapAs($this->mapper);
		$mapping->getGateway()->getQueryExecutor()->attach(new QueryLogger());
		$this->objectManager = $mapping->getObjects();
	}

	protected function tearDown() {
		$this->conn->exec(PQ_TEST_TEARDOWN_SQL);
	}

	function testBasic() {
		$row = $this->objectManager->getMap()->getGateway()->find(["id="=>1])->current();
		$row_id = $this->objectManager->rowId($row);
		$this->assertFalse($this->objectManager->hasObject($row_id));
		$this->objectManager->createObject($row);
		$this->assertTrue($this->objectManager->hasObject($row_id));
	}

	function testGetObject() {
		$row = $this->objectManager->getMap()->getGateway()->find(["id="=>1])->current();
		$row_id = $this->objectManager->rowId($row);
		$this->objectManager->createObject($row);
		$this->assertTrue($this->objectManager->hasObject($row_id));
		$this->assertInstanceof(TestModel::class, $this->objectManager->getObject($row));
	}

	/**
	 * @expectedException BadMethodCallException
	 */
	function testGetObjectException() {
		$row = $this->objectManager->getMap()->getGateway()->find(["id="=>1])->current();
		$this->objectManager->getObject($row);
	}

	function testReset() {
		$row = $this->objectManager->getMap()->getGateway()->find(["id="=>1])->current();
		$row_id = $this->objectManager->rowId($row);
		$this->assertFalse($this->objectManager->hasObject($row_id));
		$this->objectManager->createObject($row);
		$this->assertTrue($this->objectManager->hasObject($row_id));
		$this->objectManager->reset();
		$this->assertFalse($this->objectManager->hasObject($row_id));
	}

	function testResetObject() {
		$row = $this->objectManager->getMap()->getGateway()->find(["id="=>1])->current();
		$row_id = $this->objectManager->rowId($row);
		$this->assertFalse($this->objectManager->hasObject($row_id));
		$this->objectManager->createObject($row);
		$this->assertTrue($this->objectManager->hasObject($row_id));
		$this->objectManager->resetObject($row);
		$this->assertFalse($this->objectManager->hasObject($row_id));
	}

	function testFalseRowId() {
		$this->assertFalse($this->objectManager->rowId(new Row($this->objectManager->getMap()->getGateway())));
	}

	function testExtractRowId() {
		$row = $this->objectManager->getMap()->getGateway()->find(["id="=>1])->current();
		$row_id = $this->objectManager->rowId($row);
		$object = $this->objectManager->getMap()->map($row);
		$this->assertEquals($row_id, $this->objectManager->extractRowId($object));
	}

	function testSerializeRowIdScalar() {
		$this->assertEquals(
			$this->objectManager->serializeRowId(["id" => 1]),
			$this->objectManager->serializeRowId($this->objectManager->serializeRowId(["id"=>1]))
		);
	}

	function testSerializeRowIdNull() {
		$this->assertEquals("null", $this->objectManager->serializeRowId(null));
		$this->assertFalse($this->objectManager->serializeRowId(null, true));
		$this->assertFalse($this->objectManager->serializeRowId(["id"=>null], true));
	}

	function testGetRow() {
		$row = $this->objectManager->getMap()->getGateway()->find(["id="=>1])->current();
		$obj = $this->objectManager->getMap()->map($row);
		$this->assertSame($row, $this->objectManager->getRow($obj));
	}

	function testAsRow() {
		$row = $this->objectManager->getMap()->getGateway()->find(["id="=>1])->current();
		$obj = $this->objectManager->getMap()->map($row);
		$this->assertSame($row, $this->objectManager->asRow($obj));
	}

	function testHasRow() {
		$row = $this->objectManager->getMap()->getGateway()->find(["id="=>1])->current();
		$obj = $this->objectManager->getMap()->map($row);
		$this->assertSame($row, $this->objectManager->asRow($obj));
		$this->assertTrue($this->objectManager->hasRow($this->objectManager->objectId($obj)));
		$this->objectManager->resetRow($obj);
		$this->assertFalse($this->objectManager->hasRow($this->objectManager->objectId($obj)));
		$this->assertNotSame($row, $this->objectManager->asRow($obj));
	}

	/**
	 * @expectedException \BadMethodCallException
	 */
	function testGetRowException() {
		$this->objectManager->getRow(new \stdClass);
	}
}
