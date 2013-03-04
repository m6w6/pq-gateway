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
print_r( $table->find(array("dt" => new \pq\Query\Expr("between %s and %s", $conn->quote("2013-03-01"), $conn->quote("2013-03-04")))) );
echo PHP_EOL;
print_r( $table->find(array("id>" => 1, "OR" => array(array("id=" => 1), array("id="=>2)))));
echo PHP_EOL;
print_r( $table->find(array("OR" => array("id>" => 1, "OR" => array(array("id=" => 1), array("id="=>2))))));
echo PHP_EOL;
print_r( $table->create(array("data" => "blabla")) );
echo PHP_EOL;
print_r( $table->create(array("data" => new \pq\Query\Expr("DEFAULT"))) );
echo PHP_EOL;
print_r( $table->update(array("id=" => 4), array("data" => "die 4")) );
echo PHP_EOL;
print_r( $table->delete(array(new \pq\Query\Expr("data is null"))) );
echo PHP_EOL;