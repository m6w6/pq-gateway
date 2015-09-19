<?php

namespace pq\Mapper\Property;

use pq\Gateway\Row;
use pq\Mapper\Mapper;
use pq\Mapper\RefProperty;
use pq\Mapper\RefPropertyInterface;

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
		$rel = $map->relOf($this->container, $this->refName);
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


	function read2(RowGateway $row) {
		#echo __METHOD__." ".$this;
		$map = $this->getRefMap();
		$rel = $this->container->getGateway()->getRelation(
			$map->getGateway()->getName(), $this->refName);
		$key = array_combine($rel->referencedColumns, array_map(function($c) use($row) {
			return $row->$c->get();
		}, $rel->foreignColumns));
		if (($obj = $this->mapper->objectOfRowId($this->refClass, $key))) {
			yield $this->property => $obj;
		} else foreach ($map->getGateway()->by($row, $this->refName) as $row) {
			yield $this->property => $this->mapper->objectOf($this->refClass, $row);
		}
	}
	
	function write2($object) {
		#echo __METHOD__." ".$this;
		$map = $this->getRefMap();
		$rel = $this->container->getGateway()->getRelation(
			$map->getGateway()->getName(), $this->refName);
		$ref = $this->extract($object);
		foreach ($rel as $fgn => $col) {
			$fld = $map->getFieldMapping($col);
			yield $fgn => $fld->extract($ref);
		}
	}
} 