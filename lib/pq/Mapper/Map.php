<?php

namespace pq\Mapper;

use pq\Gateway\Row;
use pq\Gateway\Rowset;
use pq\Gateway\Table;
use pq\Gateway\Table\Reference;
use pq\Query\Expr;

class Map implements MapInterface
{
	/**
	 * @var string
	 */
	private $class;

	/**
	 * @var Table
	 */
	private $gateway;

	/**
	 * @var ObjectManager
	 */
	private $objects;

	/**
	 * @var PropertyInterface[]
	 */
	private $properties;

	/**
	 * Create a new object map definition
	 * @param string $class
	 * @param Table $gateway
	 * @param ...PropertyInterface $properties
	 */
	function __construct($class, Table $gateway, PropertyInterface ...$properties) {
		$this->class = $class;
		$this->gateway = $gateway;
		$this->properties = $properties;
		foreach ($properties as $property) {
			$property->setContainer($this);
		}
		$this->objects = new ObjectManager($this);
	}

	/**
	 * Get the name of the mapped class
	 * @return string
	 */
	function getClass() {
		return $this->class;
	}

	/**
	 * Get the object manager
	 * @return ObjectManager
	 */
	function getObjects() {
		return $this->objects;
	}

	/**
	 * Get the underlying table gateway
	 * @return Table
	 */
	function getGateway() {
		return $this->gateway;
	}

	/**
	 * Get the defined properties to map
	 * @return PropertyInterface[]
	 */
	function getProperties() {
		return $this->properties;
	}

	/**
	 * Add a property to map
	 * @param PropertyInterface $property
	 * @return Map
	 */
	function addProperty(PropertyInterface $property) {
		$property->setContainer($this);
		$this->properties[] = $property;
		return $this;
	}

	/**
	 * Get all child rows by foreign key
	 * @param Row $row
	 * @param string $refName
	 * @param array $objects
	 * @return Rowset
	 */
	function allOf(Row $row, $refName, &$objects = null) {
		/* apply objectOf to populate the object cache */
		return $this->gateway->of($row, $refName)->apply(function($row) use(&$objects) {
			$objects[] = $this->objects->asObject($row);
		});
	}

	/**
	 * Get the parent row by foreign key
	 * @param Row $row
	 * @param string $refName
	 * @param array $objects
	 * @return Rowset
	 */
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

	/**
	 * Get the table relation reference
	 * @param MapInterface $map
	 * @param string $refName
	 * @return Reference
	 */
	function relOf(MapInterface $map, $refName) {
		return $map->getGateway()->getRelation(
			$this->gateway->getName(), $refName);
	}

	/**
	 * Drain the deferred callback queue
	 * @param callable[] $deferred
	 * @param callable $exec
	 */
	private function drain(array $deferred, callable $exec) {
		while ($deferred) {
			$cb = array_shift($deferred);
			if (($cb = $exec($cb))) {
				$deferred[] = $cb;
			}
		}
	}

	/**
	 * Map a row to an object
	 * @param Row $row
	 * @return object
	 */
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

	/**
	 * Map a rowset to an array of objects
	 * @param Rowset $rows
	 * @return object[]
	 */
	function mapAll(Rowset $rows) {
		$objects = [];
		foreach ($rows as $row) {
			$objects[] = $this->map($row);
		}
		return $objects;
	}

	/**
	 * Unmap on object
	 * @param object $object
	 */
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
