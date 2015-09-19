<?php

namespace pq\Mapper;

interface StorageInterface
{
	function find($where, $order = null, $limit = null, $offset = null);
	function delete($object);
	function save($object);
}
