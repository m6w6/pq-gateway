<?php

namespace pq\Mapper;

use OutOfBoundsException;
use pq\Exception\BadMethodCallException;
use pq\Gateway\Row;

class ObjectCache
{
	private $map;
	private $obj = [];
	private $row = [];

	function __construct(MapInterface $map) {
		$this->map = $map;
	}

	function reset() {
		$this->obj = [];
		$this->row = [];
	}

	function rowId(Row $row, $check = false) {
		try {
			$identity = $row->getIdentity();
		} catch (OutOfBoundsException $e) {
			return false;
		}
		return $this->serializeRowId($identity, $check);
	}

	function objectId($object) {
		return spl_object_hash($object);
	}

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

	function hasObject($row_id) {
		return isset($this->obj[$row_id]);
	}

	function createObject(Row $row) {
		$rid = $this->rowId($row);
		$cls = $this->map->getClass();
		$obj = new $cls;
		$oid = $this->objectId($obj);
		$this->obj[$rid] = $obj;
		$this->row[$oid] = $row;
		return $obj;
	}

	function resetObject(Row $row) {
		unset($this->obj[$this->rowId($row)]);
	}

	function getObject(Row $row) {
		$id = $this->rowId($row);
		return $this->getObjectById($id);
	}

	function getObjectById($row_id) {
		if (!$this->hasObject($row_id)) {
			throw new BadMethodCallException("Object of row with id $row_id does not exist");
		}
		return $this->obj[$row_id];
	}

	function asObject(Row $row){
		return $this->hasObject($this->rowId($row))
			? $this->getObject($row)
			: $this->createObject($row);
	}

	function hasRow($obj_id) {
		return isset($this->row[$obj_id]);
	}

	function createRow($object) {
		$oid = $this->objectId($object);
		$row = new Row($this->map->getGateway());
		$this->row[$oid] = $row;
		return $row;
	}

	function resetRow($object) {
		unset($this->row [$this->objectId($object)]);
	}
	
	function getRow($object) {
		$id = $this->objectId($object);

		if (!$this->hasRow($id)) {
			throw new BadMethodCallException("Row for object with id $id does not exist");
		}
		return $this->row[$id];
	}

	function asRow($object) {
		return $this->hasRow($this->objectId($object))
			? $this->getRow($object)
			: $this->createRow($object);
	}
}