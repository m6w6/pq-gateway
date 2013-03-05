<?php

require "./pq/Query/Expr.php";
require "./pq/Query/Writer.php";
require "./pq/Gateway/Table.php";
require "./pq/Gateway/Rowset.php";
require "./pq/Gateway/Row.php";
require "./pq/Gateway/Cell.php";

class FooModel extends \pq\Gateway\Table {
	function __construct(\pq\Connection $c) {
		parent::__construct($c, "foo", "FooCollection");
	}
}

class Foo extends \pq\Gateway\Row {
	
}

class FooCollection extends \pq\Gateway\Rowset {
	function __construct(\pq\Gateway\Table $table, \pq\Result $result) {
		parent::__construct($table, $result, "Foo");
	}
}

$conn = new \pq\Connection;
$types = new \pq\Types($conn);
$table = new FooModel($conn);
$rowset = $table->find(array(array("id>" => 1), array("id IS" => new pq\Query\Expr("NULL"))));
$current = $rowset->current();
$current->dt->mod(new pq\Query\Expr("- interval '3 day'"));
$current->update();
var_dump($current);
echo PHP_EOL;