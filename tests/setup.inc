<?php

use pq\Connection;
use pq\Gateway\Table;
use pq\Mapper\Map;
use pq\Mapper\MapInterface;
use pq\Mapper\Mapper;
use pq\Query\ExecutorInterface;
use pq\Query\Writer;

ini_set("date.timezone", "UTC");
ini_set("error_reporting", E_ALL);

define("PQ_TEST_DSN", getenv("PQ_TEST_DSN"));

const PQ_TEST_SETUP_SQL = <<<SQL
	create extension if not exists hstore;
	
	drop table if exists test cascade;
	create table test (
		id serial primary key, 
		created timestamp, 
		counter int, 
		number decimal, 
		data text,
		list int[],
		prop hstore
	);
	
	drop table if exists reftest cascade;
	create table reftest (
		test_id integer not null references test on delete cascade,
		another_test_id integer not null references test on delete cascade,
		primary key(test_id, another_test_id)
	);
	
	insert into test values (default, 'yesterday', -1, -1.1, 'yesterday', '{-1,0,1}');
	insert into test values (default, 'today', 0, 0, 'today', '{0,1,2}');
	insert into test values (default, 'tomorrow', 1, 1.1, 'tomorrow', '{1,2,3}');
	
	insert into reftest values (1,3);
	insert into reftest values (2,2);
	insert into reftest values (3,1);
SQL;

const PQ_TEST_TEARDOWN_SQL = <<<SQL
	drop table if exists test cascade;
	drop table if exists reftest cascade;
SQL;

require_once __DIR__ . "/../vendor/autoload.php";

function executeInConcurrentTransaction(ExecutorInterface $exec, $sql, array $params = null) {
	$conn = $exec->getConnection();
	$exec->setConnection(new Connection(PQ_TEST_DSN));
	$exec->execute(new Writer($sql, $params), function(){});
	$exec->setConnection($conn);
}

class QueryLogger implements SplObserver
{
	protected $fp;
	
	function __construct($logfile = null) {
		if (!isset($logfile)) {
			$logfile = __DIR__."/query.log";
		}
		if (!$this->fp = @fopen($logfile, "a")) {
			throw new RuntimeException(error_get_last()["message"]);
		}
	}
	
	function __destruct() {
		if (is_resource($this->fp)) {
			fclose($this->fp);
		}
	}
	
	function update(SplSubject $executor) {
		$result = $executor->getResult();
		if (isset($result)) {
			fprintf($this->fp, "[%s] R %s\n", 
				date_create()->format("Y-m-d H:i:s"),
				json_encode($result));
		} elseif (($query = $executor->getQuery())) {
			fprintf($this->fp, "[%s] Q %s %% %s\n", 
				date_create()->format("Y-m-d H:i:s"),
				preg_replace("/\s+/", " ", $query), 
				json_encode($query->getParams()));
		}
	}
}

class TestModel implements JsonSerializable {
	private $id, $created, $counter, $number, $data, $list, $prop;
	private $ref1, $ref2;

	function jsonSerialize() {
		return get_object_vars($this);
	}

	function __get($p) {
		return $this->$p;
	}

	function __set($p, $v) {
		$this->$p = $v;
	}

	/**
	 * @param Mapper $mapper
	 * @return MapInterface
	 */
	static function mapAs(Mapper $mapper) {
		return new Map(
			__CLASS__,
			new Table("test"),
			$mapper->mapField("id"),
			$mapper->mapField("created"),
			$mapper->mapField("counter"),
			$mapper->mapField("number"),
			$mapper->mapField("data"),
			$mapper->mapField("list"),
			$mapper->mapField("prop"),
			$mapper->mapAll("ref1")->to(RefTestModel::class)->by("test"),
			$mapper->mapAll("ref2")->to(RefTestModel::class)->by("another_test")
		);
	}
}

class RefTestModel
{
	private $pk1, $pk2;
	private $one, $two;

	function __get($p) {
		return $this->$p;
	}

	function __set($p, $v) {
		$this->$p = $v;
	}
	
	static function mapAs($mapper) {
		return new Map(
			__CLASS__,
			new Table("reftest"),
			$mapper->mapField("pk1", "test_id"),
			$mapper->mapField("pk2", "another_test_id"),
			$mapper->mapRef("one")->to(TestModel::class)->by("test"),
			$mapper->mapRef("two")->to(TestModel::class)->by("another_test")
		);
	}
}