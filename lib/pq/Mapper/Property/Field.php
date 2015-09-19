<?php

namespace pq\Mapper\Property;

use pq\Gateway\Row;

use pq\Mapper\Mapper;
use pq\Mapper\Property;
use pq\Mapper\PropertyInterface;

class Field implements PropertyInterface
{
	use Property;

	function __construct(Mapper $mapper, $property, $field = null) {
		$this->mapper = $mapper;
		$this->property = $property;
		$this->field = $field ?: $property;
	}

	function read(Row $row, $objectToUpdate) {
		/* @var $val \pq\Gateway\Cell */
		$val = $row->{$this->field};
		$this->assign($objectToUpdate, $val->get());
	}

	function write($object, Row $rowToUpdate) {
		$val = $this->extract($object);
		$rowToUpdate->{$this->field} = $val;
	}
}