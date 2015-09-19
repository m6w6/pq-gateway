<?php

namespace pq\Mapper;

interface StorageInterface
{
	/**
	 * @return pq\Mapper\Mapper
	 */
	function getMapper();
	
	function find($where, $order = null, $limit = null, $offset = null);
	function delete($object);
	function persist($object);
}