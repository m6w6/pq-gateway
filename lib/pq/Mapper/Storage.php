<?php

namespace pq\Mapper;

class Storage implements StorageInterface
{
	/**
	 *
	 * @var pq\Mapper\MapInterface
	 */
	private $map;

	/**
	 * @var \pq\Gateway\Table
	 */
	private $gateway;
	
	function __construct(MapInterface $map) {
		$this->map = $map;
		$this->gateway = $map->getGateway();
	}
	
	function find($where = [], $order = null, $limit = null, $offset = null) {
		/* @var pq\Gateway\Rowset $rowset */
		$rowset = $this->gateway->find($where, $order, $limit, $offset);
		return $this->map->mapAll($rowset);
	}
	
	function delete($object) {
		$cache = $this->map->getObjects();
		$row = $cache->asRow($object)->delete();
		$cache->resetObject($row);
		$cache->resetRow($object);
	}
	
	function save($object) {
		$this->map->unmap($object);
	}
	
}