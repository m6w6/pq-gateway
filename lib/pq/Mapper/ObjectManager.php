<?php

namespace pq\Mapper;

use OutOfBoundsException;
use pq\Exception\BadMethodCallException;
use pq\Gateway\Row;

class ObjectManager
{
	/**
	 * @var MapInterface
	 */
	private $map;

	/**
	 * @var object[]
	 */
	private $obj = [];

	/**
	 * @var Row[]
	 */
	private $row = [];

	/**
	 * Create a new ObjectManager for a mapping
	 * @param MapInterface $map
	 */
	function __construct(MapInterface $map) {
		$this->map = $map;
	}

	/**
	 * Reset all managed objects
	 */
	function reset() {
		$this->obj = [];
		$this->row = [];
	}

	/**
	 * Get the serialized row identity
	 *
	 * When $check is true, the identity will only be serialized if all columns
	 * of the primary key are set.
	 * 
	 * @param Row $row
	 * @param bool $check
	 * @return string|false serialized row id or false on failure
	 */
	function rowId(Row $row, $check = false) {
		try {
			$identity = $row->getIdentity();
		} catch (OutOfBoundsException $e) {
			return false;
		}
		return $this->serializeRowId($identity, $check);
	}

	/**
	 * Get an object's identity
	 * @param object $object
	 * @return string
	 */
	function objectId($object) {
		return spl_object_hash($object);
	}

	/**
	 * Extract a row's identity from a mapped object
	 * @param object $object
	 * @return string serialized row identity
	 */
	function extractRowId($object) {
		$id = [];
		foreach ($this->map->getGateway()->getIdentity() as $col) {
			foreach ($this->map->getProperties() as $property) {
				if ($property->exposes($col)) {
					$id[$col] = $property->extract($object);
				}
			}
		}
		return $this->serializeRowId($id, true);
	}

	/**
	 * Serialize a row's identity
	 * @param mixed $identity
	 * @param bool $check
	 * @return string|false the serialized row identity or false on failure
	 */
	function serializeRowId($identity, $check = false) {
		if (is_scalar($identity)) {
			return $identity;
		}

		if ($check && !isset($identity)) {
			return false;
		}

		if (is_array($identity)) {
			if ($check && array_search(null, $identity, true)) {
				return false;
			}
			/* one level is better than no level */
			asort($identity);
		}
		return json_encode($identity);
	}

	/**
	 * Check whether a mapped object is already cached in the manager
	 * @param string $row_id
	 * @return bool
	 */
	function hasObject($row_id) {
		return isset($this->obj[$row_id]);
	}

	/**
	 * Create a mapped object from $row
	 * @param Row $row
	 * @return object
	 */
	function createObject(Row $row) {
		$rid = $this->rowId($row);
		$cls = $this->map->getClass();
		$obj = new $cls;
		$oid = $this->objectId($obj);
		$this->obj[$rid] = $obj;
		$this->row[$oid] = $row;
		return $obj;
	}

	/**
	 * Forget the mapped object of $row
	 * @param Row $row
	 */
	function resetObject(Row $row) {
		unset($this->obj[$this->rowId($row)]);
	}

	/**
	 * Get the mapped object of $row
	 * @param Row $row
	 * @return object
	 */
	function getObject(Row $row) {
		$id = $this->rowId($row);
		return $this->getObjectById($id);
	}

	/**
	 * Get the mapped object of $row
	 * @param string $row_id
	 * @return object
	 * @throws BadMethodCallException
	 */
	function getObjectById($row_id) {
		if (!$this->hasObject($row_id)) {
			throw new BadMethodCallException("Object of row with id $row_id does not exist");
		}
		return $this->obj[$row_id];
	}

	/**
	 * Check for a mapped object of $row, and create if necessary
	 * @param Row $row
	 * @return object
	 */
	function asObject(Row $row){
		return $this->hasObject($this->rowId($row))
			? $this->getObject($row)
			: $this->createObject($row);
	}

	/**
	 * Check whether a row for a mapped object exists
	 * @param string $obj_id
	 * @return Row
	 */
	function hasRow($obj_id) {
		return isset($this->row[$obj_id]);
	}

	/**
	 * Initialize a Row from a mapped object
	 * @param object $object
	 * @return Row
	 */
	function createRow($object) {
		$oid = $this->objectId($object);
		$row = new Row($this->map->getGateway());
		$this->row[$oid] = $row;
		return $row;
	}

	/**
	 * Forget about a row of a mapped object
	 * @param object $object
	 */
	function resetRow($object) {
		unset($this->row [$this->objectId($object)]);
	}

	/**
	 * Get the row of a mapped object
	 * @param object $object
	 * @return Row
	 * @throws BadMethodCallException
	 */
	function getRow($object) {
		$id = $this->objectId($object);

		if (!$this->hasRow($id)) {
			throw new BadMethodCallException("Row for object with id $id does not exist");
		}
		return $this->row[$id];
	}

	/**
	 * Check for a row of a mapped object, create from object if neccessary
	 * @param object $object
	 * @return Row
	 */
	function asRow($object) {
		return $this->hasRow($this->objectId($object))
			? $this->getRow($object)
			: $this->createRow($object);
	}
}
