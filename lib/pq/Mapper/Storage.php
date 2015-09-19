<?php

namespace pq\Mapper;

class Storage implements StorageInterface
{
	/**
	 * @var string
	 */
	private $class;

	/**
	 * @var \pq\Mapper\Mapper
	 */
	private $mapper;

	/**
	 *
	 * @var pq\Mapper\MapInterface
	 */
	private $mapping;

	/**
	 * @var \pq\Gateway\Table
	 */
	private $gateway;
	
	function __construct(Mapper $mapper, $class) {
		$this->class = $class;
		$this->mapper = $mapper;
		$this->mapping = $mapper->mapOf($class);
		$this->gateway = $this->mapping->getGateway();
	}
	
	function getMapper() {
		return $this->mapper;
	}
	
	function find($where = [], $order = null, $limit = null, $offset = null) {
		/* @var pq\Gateway\Rowset $rowset */
		$rowset = $this->gateway->find($where, $order, $limit, $offset);
		return $this->mapping->mapAll($rowset);
	}
	
	function delete($object) {
		$cache = $this->mapping->getObjects();
		$row = $cache->asRow($object)->delete();
		$cache->resetObject($row);
		$cache->resetRow($object);
	}
	
	function persist($object) {
		$this->mapping->unmap($object);
	}
	
}