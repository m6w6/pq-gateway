<?php

namespace pq\Mapper\Property;

use pq\Gateway\Cell;
use pq\Gateway\Row;
use pq\Mapper\Mapper;
use pq\Mapper\Property;
use pq\Mapper\PropertyInterface;

class Field implements PropertyInterface
{
	use Property;

	/**
	 * Create a simple field mapping
	 * @param Mapper $mapper
	 * @param string $property
	 * @param string $field
	 */
	function __construct(Mapper $mapper, $property, $field = null) {
		$this->mapper = $mapper;
		$this->property = $property;
		$this->field = $field ?: $property;
	}

	/**
	 * Read property value
	 * @param Row $row
	 * @param object $objectToUpdate
	 */
	function read(Row $row, $objectToUpdate) {
		/* @var $val Cell */
		$val = $row->{$this->field};
		$this->assign($objectToUpdate, $val->get());
	}

	/**
	 * Write property value
	 * @param object $object
	 * @param Row $rowToUpdate
	 */
	function write($object, Row $rowToUpdate) {
		$val = $this->extract($object);
		$rowToUpdate->{$this->field} = $val;
	}
}