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

	/**
	 * Create a child rows mapping
	 * @param Mapper $mapper
	 * @param string $property
	 */
	function __construct(Mapper $mapper, $property) {
		$this->mapper = $mapper;
		$this->property = $property;
	}

	/**
	 * Read the child objects
	 * @param Row $row
	 * @param object $objectToUpdate
	 */
	function read(Row $row, $objectToUpdate) {
		$val = $this->extract($objectToUpdate);
		if (!isset($val)) {
			$map = $this->mapper->mapOf($this->refClass);
			$all = $map->allOf($row, $this->refName, $objects);
			$this->assign($objectToUpdate, $objects);
			$map->mapAll($all);
		}
	}

	/**
	 * Write the child rows
	 * @param object $object
	 * @param Row $rowToUpdate
	 * @return callable deferred callback
	 */
	function write($object, Row $rowToUpdate) {
		if (($refs = $this->extract($object))) {
			$property = $this->findRefProperty($object);
			foreach ($refs as $ref) {
				$property->assign($ref, $object);
			}
			return function() use($refs) {
				$map = $this->mapper->mapOf($this->refClass);
				foreach ($refs as $ref) {
					$map->unmap($ref);
				}
			};
		}
	}

	/**
	 * Find the referring property that references $object on our foreign key
	 * @param object $object
	 * @return RefPropertyInterface[]
	 * @throws UnexpectedValueException
	 */
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
}
