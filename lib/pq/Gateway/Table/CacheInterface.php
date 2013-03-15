<?php

namespace pq\Gateway\Table;

interface CacheInterface
{
	/**
	 * Set an item
	 * @param string $key
	 * @param mixed $val
	 * @param int $ttl
	 * @return \pq\Gateway\Table\CacheInterface
	 */
	function set($key, $val, $ttl = 0);
	
	/**
	 * Get an item
	 * @param string $key
	 * @param bool $exists
	 * @return mixed
	 */
	function get($key, &$exists = null);
	
	/**
	 * Delete an item
	 * @param string $key
	 * @return \pq\Gateway\Table\CacheInterface
	 */
	function del($key);
}