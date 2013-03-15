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
