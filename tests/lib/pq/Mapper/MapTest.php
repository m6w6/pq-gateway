<?php

namespace pq\Mapper;

use pq\Connection;
use pq\Gateway\Table;
use RefTestModel;
use stdClass;
use TestModel;
use UnexpectedValueException;

require_once __DIR__."/../../../setup.inc";

class MapTest extends \PHPUnit_Framework_TestCase
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

	function testMap() {
		$row = $this->map->getGateway()->find(["id="=>1])->current();
		$obj = $this->map->map($row);
		$this->assertEquals($row->data->get(), $obj->data);
	}

	function testUnmap() {
		$obj = new \TestModel;
		$obj->data = "this is a test";
		$this->map->unmap($obj);
		$this->assertSame(4, $obj->id);
	}

	function testUnmapRef() {
		$obj = new \TestModel;
		$obj->ref1 = $obj->ref2 = [
			new \RefTestModel
		];
		$this->map->unmap($obj);
	}
}
