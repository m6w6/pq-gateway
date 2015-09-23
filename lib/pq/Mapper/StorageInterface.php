<?php

namespace pq\Mapper;

interface StorageInterface
{
	/**
	 * Find by PK
	 * @param mixed $pk
	 * @return object
	 */
	function get($pk);
	
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

	/**
	 * Buffer in a transaction
	 */
	function buffer();

	/**
	 * Commit
	 */
	function flush();

	/**
	 * Rollback
	 */
	function discard();
}
