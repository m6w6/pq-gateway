<?php

namespace pq\Mapper\Property;

use pq\Gateway\Row;
use pq\Mapper\Mapper;
use pq\Mapper\PropertyInterface;
use pq\Mapper\RefProperty;
use pq\Mapper\RefPropertyInterface;
use UnexpectedValueException;

class Ref implements RefPropertyInterface
{
	use RefProperty;

	/**
	 * Create a parent row mapping
	 * @param Mapper $mapper
	 * @param string $property
	 */
	function __construct(Mapper $mapper, $property) {
		$this->mapper = $mapper;
		$this->property = $property;
	}

	/**
	 * Read the parent object
	 * @param Row $row
	 * @param object $objectToUpdate
	 */
	function read(Row $row, $objectToUpdate) {
		$val = $this->extract($objectToUpdate);
		if (!isset($val)) {
			$map = $this->mapper->mapOf($this->refClass);
			$ref = $map->refOf($row, $this->refName, $objects)->current();
			$this->assign($objectToUpdate, current($objects));
			$map->map($ref);
		}
	}

	/**
	 * Write the parent row's foreign key
	 * @param object $object
	 * @param Row $rowToUpdate
	 * @throws UnexpectedValueException
	 */
	function write($object, Row $rowToUpdate) {
		if (!$ref = $this->extract($object)) {
			return;
		}
		$map = $this->mapper->mapOf($this->refClass);
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

	/**
	 * Find the property exposing $col
	 * @param string $col
	 * @return PropertyInterface[]
	 */
	private function findFieldProperty($col) {
		$map = $this->mapper->mapOf($this->refClass);
		return array_filter($map->getProperties(), function($property) use($col) {
			return $property->exposes($col);
		});
	}
} 