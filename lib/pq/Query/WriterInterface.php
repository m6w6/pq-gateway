<?php

namespace pq\Query;

interface WriterInterface
{
	function __toString();
	function getParams();
	function getTypes();
	function write(/*...*/);
	function param($param, $type = null);
	function criteria(array $criteria);
	function reset();
	function exec(\pq\Connection $c);
}
