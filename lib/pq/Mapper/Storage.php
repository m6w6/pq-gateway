<?php

namespace pq\Mapper;

use InvalidArgumentException;
use pq\Connection;
use pq\Gateway\Table;
use pq\Transaction;

class Storage implements StorageInterface
{
	/**
	 * The mapping of this storage
	 * @var MapInterface
	 */
	private $map;

	/**
	 * The mapper
	 * @var Mapper
	 */
	private $mapper;

	/**
	 * The underlying table gateway
	 * @var Table
	 */
	private $gateway;

	/**
	 * Create a storage for $map
	 * @param Mapper $mapper
	 * @param string $class
	 */
	function __construct(Mapper $mapper, $class) {
		$this->mapper = $mapper;
		$this->map = $mapper->mapOf($class);
		$this->gateway = $this->map->getGateway();
	}

	/**
	 * Find by PK
	 * @param mixed $pk
	 * @return object
	 */
	function get($pk) {
		$id = $this->gateway->getIdentity();
		if (count($id) == 1 && is_scalar($pk)) {
			$vals = [$pk];
		} elseif (is_array($pk) && count($pk) === count($id)) {
			$vals = $pk;
		} else {
			throw InvalidArgumentException(
				"Insufficient identity provided; not all fields of %s are provided in %s",
				json_encode($id->getColumns()), json_encode($pk));
		}

		$keys = array_map(function($v) {
			return "$v=";
		}, $id->getColumns());

		$rowset = $this->gateway->find(array_combine($keys, $vals));
		
		return $this->map->map($rowset->current());
	}

	/**
	 * Find
	 * @param array $where
	 * @param string $order
	 * @param int $limit
	 * @param int $offset
	 * @return object[]
	 */
	function find($where = [], $order = null, $limit = null, $offset = null) {
		/* @var pq\Gateway\Rowset $rowset */
		$rowset = $this->gateway->find($where, $order, $limit, $offset);
		return $this->map->mapAll($rowset);
	}

	/**
	 * Find parent
	 * @param object $object
	 * @param string $refName
	 * @return object
	 */
	function by($object, $refName) {
		$row = $this->mapper->mapOf($object)->getObjects()->getRow($object);
		$this->map->refOf($row, $refName, $objects);
		return current($objects);
	}

	/**
	 * Find childs
	 * @param object $object
	 * @param string $refName
	 * @return array
	 */
	function of($object, $refName) {
		$row = $this->mapper->mapOf($object)->getObjects()->getRow($object);
		$this->map->allOf($row, $refName, $objects);
		return $objects;
	}

	/**
	 * Delete
	 * @param object $object
	 */
	function delete($object) {
		$cache = $this->map->getObjects();
		$row = $cache->asRow($object)->delete();
		$cache->resetObject($row);
		$cache->resetRow($object);
	}

	/**
	 * Save
	 * @param object $object
	 */
	function save($object) {
		$this->map->unmap($object);
	}

	/**
	 * Buffer in a transaction
	 */
	function buffer() {
		switch ($this->gateway->getConnection()->transactionStatus) {
		case Connection::TRANS_INTRANS:
			break;
		default:
			$this->gateway->getQueryExecutor()->execute(new \pq\Query\Writer("START TRANSACTION"));
		}
	}

	/**
	 * Commit
	 */
	function flush() {
		switch ($this->gateway->getConnection()->transactionStatus) {
		case Connection::TRANS_IDLE:
			break;
		default:
			$this->gateway->getQueryExecutor()->execute(new \pq\Query\Writer("COMMIT"));
		}
	}

	/**
	 * Rollback
	 */
	function discard() {
		switch ($this->gateway->getConnection()->transactionStatus) {
		case Connection::TRANS_IDLE:
			break;
		default:
			$this->gateway->getQueryExecutor()->execute(new \pq\Query\Writer("ROLLBACK"));
		}
		$this->map->getObjects()->reset();
	}
}
