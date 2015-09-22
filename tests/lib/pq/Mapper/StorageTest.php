<?php

namespace pq\Mapper;

use PHPUnit_Framework_TestCase;
use pq\Connection;
use pq\Gateway\Table;
use QueryLogger;
use RefTestModel;
use TestModel;

require_once __DIR__."/../../../setup.inc";

class StorageTest extends PHPUnit_Framework_TestCase
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
	 * @var Storage
	 */
	protected $storage;

	protected function setUp() {
		$this->conn = new Connection(PQ_TEST_DSN);
		$this->conn->exec(PQ_TEST_SETUP_SQL);
		Table::$defaultConnection = $this->conn;
		$this->mapper = new Mapper;
		$this->mapper->mapOf(TestModel::class)->getGateway()->getQueryExecutor()->attach(new QueryLogger());
		$this->mapper->mapOf(RefTestModel::class)->getGateway()->getQueryExecutor()->attach(new QueryLogger());
		$this->storage = $this->mapper->createStorage(TestModel::class);
	}

	protected function tearDown() {
		$this->conn->exec(PQ_TEST_TEARDOWN_SQL);
	}

	function testFind() {
		$objects = $this->storage->find();
		for ($i = 0; $i < count($objects); ++$i) {
			$this->assertSame($i+1, $objects[$i]->id);
		}
	}

	function testSave() {
		$test = new TestModel;
		$test->ref1 = $test->ref2 = [
			new RefTestModel
		];
		$this->storage->save($test);

		$this->mapper->mapOf(TestModel::class)->getObjects()->reset();
		$this->mapper->mapOf(RefTestModel::class)->getObjects()->reset();

		$this->assertEquals([$test], $this->storage->find(["id="=>$test->id]));
	}

	function testDelete() {
		$obj = current($this->storage->find());
		$this->storage->delete($obj);
		$this->mapper->mapOf(TestModel::class)->getObjects()->resetRow($obj);
		$this->assertCount(0, $this->storage->find(["id="=>$obj->id]));
	}
}
