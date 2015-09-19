<?php

namespace pq\Mapper;

use pq\Gateway\Row;
use pq\Gateway\Rowset;
use pq\Gateway\Table;
use pq\Query\Expr;

class Map implements MapInterface
{
	private $class;
	private $gateway;
	private $objects;
	private $properties;

	function __construct($class, Table $gateway, PropertyInterface ...$properties) {
		$this->class = $class;
		$this->gateway = $gateway;
		$this->properties = $properties;
		foreach ($properties as $property) {
			$property->setContainer($this);
		}
		$this->objects = new ObjectCache($this);
	}

	function getClass() {
		return $this->class;
	}

	function getObjects() {
		return $this->objects;
	}

	/**
	 * @return Table
	 */
	function getGateway() {
		return $this->gateway;
	}

	function getProperties() {
		return $this->properties;
	}

	function addProperty(PropertyInterface $property) {
		$property->setContainer($this);
		$this->properties[] = $property;
		return $this;
	}
/*
	function idOf(Row $row, $check = false) {
		$identity = $row->getIdentity();
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
			/* one level is better than no level * /
			asort($identity);
		}
		return json_encode($identity);
	}
	
	function objectOf(Row $row) {
		$id = $this->idOf($row);

		if (isset($this->objects["obj"][$id])) {
			$obj = $this->objects["obj"][$id];
		} else {
			$obj = new $this->class;
			$this->objects["obj"][$id] = $obj;
			$this->objects["row"][spl_object_hash($obj)] = $row;
		}
		return $obj;
	}

	function rowOf($object) {
		$id = spl_object_hash($object);

		if (isset($this->objects["row"][$id])) {
			$row = $this->objects["row"][$id];
		} else {
			$row = new Row($this->gateway);
			$this->objects["row"][$id] = $row;
		}
		return $row;
	}
*/
	function allOf(Row $row, $refName, &$objects = null) {
		/* apply objectOf to populate the object cache */
		return $this->gateway->of($row, $refName)->apply(function($row) use(&$objects) {
			$objects[] = $this->objects->asObject($row);
		});
	}

	function refOf(Row $row, $refName, &$objects = null) {
		$rid = [];
		$rel = $row->getTable()->getRelation($this->gateway->getName(), $refName);
		// FIXME: check if foreign key points to primary key
		foreach ($rel as $fgn => $col) {
			$rid[$col] = $row->$fgn->get();
		}
		$rid = $this->objects->serializeRowId($rid);
		if ($this->objects->hasObject($rid)) {
			$object = $this->objects->getObjectById($rid);
			$row = $this->objects->getRow($object);
			$objects[] = $object;
			$rowset = new Rowset($this->gateway);
			return $rowset->append($row);
		}
		/* apply objectOf to populate the object cache */
		return $this->gateway->by($row, $refName)->apply(function($row) use(&$objects) {
			$objects[] = $this->objects->asObject($row);
		});
	}

	function relOf(MapInterface $map, $refName) {
		return $map->getGateway()->getRelation(
			$this->gateway->getName(), $refName);
	}

	private function drain(array $deferred, callable $exec) {
		while ($deferred) {
			$cb = array_shift($deferred);
			if (($cb = $exec($cb))) {
				$deferred[] = $cb;
			}
		}
	}

	function map(Row $row) {
		$deferred = [];
		$object = $this->objects->asObject($row);
		foreach ($this->properties as $property) {
			if (($cb = $property->read($row, $object))) {
				$deferred[] = $cb;
			}
		}
		$this->drain($deferred, function(callable $cb) use($row, $object) {
			return $cb($row, $object);
		});
		return $object;
	}

	function mapAll(Rowset $rows) {
		$objects = [];
		foreach ($rows as $row) {
			$objects[] = $this->map($row);
		}
		return $objects;
	}

	function unmap($object) {
		$deferred = [];
		/* @var $row Row */
		$row = $this->objects->asRow($object);
		$upd = $this->objects->rowId($row, true);
		foreach ($this->properties as $property) {
			if (($cb = $property->write($object, $row))) {
				$deferred[] = $cb;
			}
		}
		foreach ($this->gateway->getIdentity() as $col) {
			if (null === $row->$col->get()
			|| ($row->$col->isExpr() && $row->$col->get()->isNull()))
			{
				$row->$col = new Expr("DEFAULT");
			}
		}
		if ($row->isDirty()) {
			if ($upd) {
				$row->update();
			} else {
				$row->create();
			}
		}
		foreach ($this->properties as $property) {
			if (($cb = $property->read($row, $object))) {
				$deferred[] = $cb;
			}
		}
		$this->drain($deferred, function($cb) use($object, $row) {
			return $cb($object, $row);
		});
		if ($row->isDirty()) {
			$row->update();
		}
	}

}