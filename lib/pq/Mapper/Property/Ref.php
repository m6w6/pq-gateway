<?php

namespace pq\Mapper\Property;

use pq\Gateway\Row;
use pq\Mapper\Mapper;
use pq\Mapper\RefProperty;
use pq\Mapper\RefPropertyInterface;
use UnexpectedValueException;

class Ref implements RefPropertyInterface
{
	use RefProperty;
	
	function __construct(Mapper $mapper, $property) {
		$this->mapper = $mapper;
		$this->property = $property;
	}

	function read(Row $row, $objectToUpdate) {
		$val = $this->extract($objectToUpdate);
		if (!isset($val)) {
			$map = $this->mapper->mapOf($this->refClass);
			$ref = $map->refOf($row, $this->refName, $objects)->current();
			$this->assign($objectToUpdate, current($objects));
			$map->map($ref);
		}
	}

	function write($object, Row $rowToUpdate) {
		$map = $this->mapper->mapOf($this->refClass);
		$ref = $this->extract($object);
		if (!$rel = $map->relOf($this->container, $this->refName)) {
			throw new UnexpectedValueException(
				sprintf("Unrelated reference from %s to %s with name %s",
					$this->container->getGateway()->getName(),
					$map->getGateway()->getName(),
					$this->refName));
		}
		foreach ($rel as $fgn => $col) {
			foreach ($this->findFieldProperty($col) as $property) {
				$value = $property->extract($ref);
				$rowToUpdate->$fgn = $value;
			}
		}
	}

	private function findFieldProperty($col) {
		$map = $this->mapper->mapOf($this->refClass);
		return array_filter($map->getProperties(), function($property) use($col) {
			return $property->exposes($col);
		});
	}
} 