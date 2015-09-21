<?php

namespace pq\Mapper;

use pq\Gateway\Table;

class Storage implements StorageInterface
{
	/**
	 * The mapping of this storage
	 * @var MapInterface
	 */
	var $map;

	/**
	 * The underlying table gateway
	 * @var Table
	 */
	private $gateway;

	/**
	 * Create a storage for $map
	 * @param MapInterface $map
	 */
	function __construct(MapInterface $map) {
		$this->map = $map;
		$this->gateway = $map->getGateway();
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
}
