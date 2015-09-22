<?php

namespace pq\Mapper;

use pq\Connection;
use pq\Gateway\Table;
use RefTestModel;
use stdClass;
use TestModel;
use UnexpectedValueException;

require_once __DIR__."/../../../setup.inc";

class MapperTest extends \PHPUnit_Framework_TestCase
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
	 * @var Map
	 */
	protected $map;

	protected function setUp() {
		$this->conn = new Connection(PQ_TEST_DSN);
		$this->conn->exec(PQ_TEST_SETUP_SQL);
		Table::$defaultConnection = $this->conn;
		$this->mapper = new Mapper;
		$this->map = TestModel::mapAs($this->mapper);
	}

	protected function tearDown() {
		$this->conn->exec(PQ_TEST_TEARDOWN_SQL);
	}

	function testBasic() {
		$this->mapper->register($this->map);
		$this->assertSame($this->map, $this->mapper->mapOf(TestModel::class));
		$this->assertInstanceOf(MapInterface::class, $this->mapper->mapOf(RefTestModel::class));
	}

	/**
	 * @expectedException UnexpectedValueException
	 */
	function testMapOfException() {
		$this->mapper->mapOf(new stdClass);
	}

	function testCreateStorage() {
		$this->assertInstanceOf(StorageInterface::class, $this->mapper->createStorage(TestModel::class));
	}

	/**
	 * @expectedException UnexpectedValueException
	 */
	function testCreateStorageException() {
		$this->assertInstanceOf(StorageInterface::class, $this->mapper->createStorage(foo::class));
	}

	function testGetReflector() {
		$o = new RefTestModel;
		$r = $this->mapper->getReflector($o, "pk1");
		$this->assertInstanceOf("ReflectionProperty", $r);
		$this->assertNull($r->getValue($o));
		$r->setValue($o, 1);
		$this->assertSame(1, $r->getValue($o));
	}
}
