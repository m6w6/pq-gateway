<?php

define("PQ_DSN", "");
define("PQ_TEST_TABLE_NAME", "test");
define("PQ_TEST_CREATE_TABLE", sprintf(
<<<SQL
	create table %s (
		id serial primary key, 
		created timestamp, 
		counter int, 
		number decimal, 
		data text
	)
SQL
	, PQ_TEST_TABLE_NAME));
define("PQ_TEST_DROP_TABLE", sprintf("drop table if exists %s", PQ_TEST_TABLE_NAME));
define("PQ_TEST_CREATE_DATA", sprintf(
<<<SQL
	insert into %1\$s values (default, 'yesterday', -1, -1.1, 'yesterday');
	insert into %1\$s values (default, 'today', 0, 0, 'today');
	insert into %1\$s values (default, 'tomorrow', 1, 1.1, 'tomorrow');
SQL
	, PQ_TEST_TABLE_NAME
));

spl_autoload_register(function($c) {
	if (substr($c,0,3) == "pq\\") return require_once sprintf("%s/../lib/%s.php", __DIR__, strtr($c, "\\", "/"));
});
