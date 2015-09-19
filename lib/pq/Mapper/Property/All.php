<?php

namespace pq\Mapper\Property;

use pq\Gateway\Row;
use pq\Mapper\Mapper;
use pq\Mapper\RefProperty;
use pq\Mapper\RefPropertyInterface;
use UnexpectedValueException;

class All implements RefPropertyInterface
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
			$all = $map->allOf($row, $this->refName, $objects);
			$this->assign($objectToUpdate, $objects);
			$map->mapAll($all);
		}
	}

	function write($object, Row $rowToUpdate) {
		$property = $this->findRefProperty($object);
		$map = $this->mapper->mapOf($this->refClass);
		$refs = $this->extract($object);
		foreach ($refs as $ref) {
			$property->assign($ref, $object);
		}
		return function() use($map, $refs) {
			foreach ($refs as $ref) {
				$map->unmap($ref);
			}
		};
		
		if (!$this->container->getObjects()->rowId($rowToUpdate, true)) {
			return [$this, "write"];
		} else {
			/* $object = User */
			/* $refs = array(Email) */
			/* $property = Property\Ref(Email::$user)->to(User)->by("email_user") */
			/* now update array(Email) with id of User, i.e. $ref->user_id = $object->id */
			$map = $this->mapper->mapOf($this->refClass);
			$refs = $this->extract($object);
			foreach ($refs as $ref) {
				$property->assign($ref, $object);
				$map->unmap($ref);
			}
		}
	}

	private function findRefProperty($object) {
		$map = $this->mapper->mapOf($this->refClass);
		$property = array_filter($map->getProperties(), function($property) use($object) {
			if ($property instanceof RefPropertyInterface) {
				return $property->references($object) && $property->on($this->refName);
			}
		});

		if (1 != count($property)) {
			// FIXME: move the decl
			throw new UnexpectedValueException(
				sprintf("%s does not reference %s exactly once through %s",
					$this->refClass, $this->container->getClass(), $this->refName));
		}
		return current($property);
	}

	function read2(RowGateway $row) {
		#echo __METHOD__." ".$this;
		$ref = $this->getRefMap()->ref($row, $this->refName);
		$value = $this->mapper->map($ref, $this->refClass);
		return [$this->property => $value];
	}
	
	function write2($object) {
		#echo __METHOD__." ".$this;
		$value = $this->extract($object);
		foreach ($value as $ref) {
			$this->mapper->queue(function() use(&$object, &$ref) {
				$map = $this->getRefMap()->getRefMapping($this->refName);
				$map->assign($ref, $object);
				$this->mapper->unmap($ref, $this->getRefMap());
			});
		}
		return [];
	}
}