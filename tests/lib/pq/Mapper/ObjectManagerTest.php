<?php

namespace pq\Mapper;

require_once __DIR__."/../../../setup.inc";

use pq\Connection;
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
	 * @var ObjectManager
	 */
	protected $objectManager;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->conn = new Connection(PQ_TEST_DSN);
		$this->conn->exec(PQ_TEST_SETUP_SQL);
		Table::$defaultConnection = $this->conn;
		$mapper = new Mapper;
		$mapping = TestModel::mapAs($mapper);
		$mapping->getGateway()->getQueryExecutor()->attach(new QueryLogger());
		$this->objectManager = new ObjectManager($mapping);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {

	}

	public function testBasic() {
		$row = $this->objectManager->getMap()->getGateway()->find(["id="=>1])->current();
		$row_id = $this->objectManager->rowId($row);
		$this->assertFalse($this->objectManager->hasObject($row_id));
		$this->objectManager->createObject($row);
		$this->assertTrue($this->objectManager->hasObject($row_id));
		$this->objectManager->reset();
		$this->assertFalse($this->objectManager->hasObject($row_id));
	}
	
	/**
	 * @covers pq\Mapper\ObjectManager::rowId
	 * @todo   Implement testRowId().
	 */
	public function testRowId() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers pq\Mapper\ObjectManager::objectId
	 * @todo   Implement testObjectId().
	 */
	public function testObjectId() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers pq\Mapper\ObjectManager::extractRowId
	 * @todo   Implement testExtractRowId().
	 */
	public function testExtractRowId() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers pq\Mapper\ObjectManager::serializeRowId
	 * @todo   Implement testSerializeRowId().
	 */
	public function testSerializeRowId() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers pq\Mapper\ObjectManager::hasObject
	 * @todo   Implement testHasObject().
	 */
	public function testHasObject() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers pq\Mapper\ObjectManager::createObject
	 * @todo   Implement testCreateObject().
	 */
	public function testCreateObject() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers pq\Mapper\ObjectManager::resetObject
	 * @todo   Implement testResetObject().
	 */
	public function testResetObject() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers pq\Mapper\ObjectManager::getObject
	 * @todo   Implement testGetObject().
	 */
	public function testGetObject() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers pq\Mapper\ObjectManager::getObjectById
	 * @todo   Implement testGetObjectById().
	 */
	public function testGetObjectById() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers pq\Mapper\ObjectManager::asObject
	 * @todo   Implement testAsObject().
	 */
	public function testAsObject() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers pq\Mapper\ObjectManager::hasRow
	 * @todo   Implement testHasRow().
	 */
	public function testHasRow() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers pq\Mapper\ObjectManager::createRow
	 * @todo   Implement testCreateRow().
	 */
	public function testCreateRow() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers pq\Mapper\ObjectManager::resetRow
	 * @todo   Implement testResetRow().
	 */
	public function testResetRow() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers pq\Mapper\ObjectManager::getRow
	 * @todo   Implement testGetRow().
	 */
	public function testGetRow() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers pq\Mapper\ObjectManager::asRow
	 * @todo   Implement testAsRow().
	 */
	public function testAsRow() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

}
