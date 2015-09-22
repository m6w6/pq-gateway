<?php

namespace pq\Gateway;

require_once __DIR__."/../../../setup.inc";

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
			$this->assertEquals($val, $row->$key->get());
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
		$refs->append($rows->seek(0)->current()->allOf("reftest")->current());
		$refs->append($rows->seek(1)->current()->allOf("reftest")->current());
		$refs->seek(0)->current()->test = $rows->seek(1)->current();
		$refs->seek(1)->current()->test = $rows->seek(0)->current();
		$refs->update();
	}
	
	public function testArray() {
		$row = $this->table->find(["id="=>1])->current();
		$this->assertEquals([-1,0,1], $row->list->get());
		$row->list[] = 4;
		$row->list[2] = null;
		$row->update();
		$this->assertEquals([-1,0,null,4], $row->list->get());
	}
	
	public function testMultiArray() {
		$row = $this->table->find(["id="=>2])->current();
		$this->assertEquals([0,1,2], $row->list->get());
		$row->list = [$row->list->get()];
		$row->update();
		$this->assertEquals([[0,1,2]], $row->list->get());
		$this->setExpectedException("PHPUnit_Framework_Error_Notice", 
			"Indirect modification of overloaded element of pq\Gateway\Cell has no effect");
		$row->list[0][0] = -1;
	}
		
	public function testHstore() {
		$this->conn->setConverter(new Hstore(new \pq\Types($this->conn)));
		$row = $this->table->find(["id="=>3])->current();
		$this->assertEquals(null, $row->prop->get());
		$data = array("foo" => "bar", "a" => 1, "b" => 2);
		$row->prop = $data;
		$row->update();
		$this->assertEquals($data, $row->prop->get());
		$row->prop["a"] = null;
		$row->update();
		$data["a"] = null;
		$this->assertEquals($data, $row->prop->get());
		unset($data["a"], $row->prop["a"]);
		$row->update();
		$this->assertEquals($data, $row->prop->get());
	}	
}

class Hstore implements \pq\Converter
{
	protected $types;
	function __construct(\pq\Types $types) {
		$this->types = $types["hstore"]->oid;
	}
	function convertTypes() {
		return [$this->types];
	}
	function convertFromString($string, $type) {
		return eval("return [$string];");
	}
	function convertToString($data, $type) {
		$string = "";
		foreach ($data as $k => $v) {
			$string .= "\"".addslashes($k)."\"=>";
			if (isset($v)) {
				$string .= "\"".addslashes($v)."\",";
			} else {
				$string .= "NULL,";
			}
		}
		return $string;
	}
}
