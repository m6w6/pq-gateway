<?php

const PQ_TEST_DSN = "";

const PQ_TEST_TABLE_CREATE = <<<SQL
	drop table if exists test cascade;
	create table test (
		id serial primary key, 
		created timestamp, 
		counter int, 
		number decimal, 
		data text
	)
SQL;

const PQ_TEST_TABLE_DROP = <<<SQL
	drop table if exists test cascade;
SQL;

const PQ_TEST_REFTABLE_CREATE = <<<SQL
	drop table if exists reftest cascade;
	create table reftest (
		test_id integer not null references test on delete cascade,
		another_test_id integer not null references test on delete cascade
	);
SQL;

const PQ_TEST_REFTABLE_DROP = <<<SQL
	drop table if exists reftest cascade;
SQL;

const PQ_TEST_DATA = <<<SQL
	insert into test values (default, 'yesterday', -1, -1.1, 'yesterday');
	insert into test values (default, 'today', 0, 0, 'today');
	insert into test values (default, 'tomorrow', 1, 1.1, 'tomorrow');
	
	insert into reftest values (1,3);
	insert into reftest values (2,2);
	insert into reftest values (3,1);
SQL;

spl_autoload_register(function($c) {
	if (substr($c,0,3) == "pq\\") return require_once sprintf("%s/../lib/%s.php", __DIR__, strtr($c, "\\", "/"));
});

function executeInConcurrentTransaction(\pq\Query\ExecutorInterface $exec, $sql, array $params = null) {
	$conn = $exec->getConnection();
	$exec->setConnection(new pq\Connection(PQ_TEST_DSN));
	$exec->execute(new \pq\Query\Writer($sql, $params), function(){});
	$exec->setConnection($conn);
}

class QueryLogger implements \SplObserver
{
	protected $fp;
	
	function __construct($logfile = null) {
		if (!isset($logfile)) {
			$logfile = __DIR__."/query.log";
		}
		if (!$this->fp = @fopen($logfile, "a")) {
			throw new \RuntimeException(error_get_last()["message"]);
		}
	}
	
	function __destruct() {
		if (is_resource($this->fp)) {
			fclose($this->fp);
		}
	}
	
	function update(\SplSubject $executor) {
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
