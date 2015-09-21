<?php

namespace pq\Mapper;

interface StorageInterface
{
	/**
	 * Find
	 * @param array $where
	 * @param string $order
	 * @param int $limit
	 * @param int $offset
	 * @return object[]
	 */
	function find($where = [], $order = null, $limit = null, $offset = null);

	/**
	 * Delete
	 * @param object $object
	 */
	function delete($object);

	/**
	 * Save
	 * @param object $object
	 */
	function save($object);
}
