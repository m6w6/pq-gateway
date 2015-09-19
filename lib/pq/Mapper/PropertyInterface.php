<?php

namespace pq\Mapper;

use pq\Gateway\Row;

interface PropertyInterface
{
	function write($object, Row $rowToUpdate);
	function read(Row $row, $objectToUpdate);

	function assign($object, $value);
	function extract($object);
	
	function getProperty();

	function getContainer();
	function setContainer(MapInterface $container);
	
	function defines($property);
	function exposes($field);

}
