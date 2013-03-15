<?php

namespace pq\Gateway\Table;

class StaticCache implements CacheInterface
{
	protected static $cache = array();
	
	/**
	 * @inheritdoc
	 * @param string $key
	 * @param bool $exists
	 * @return mixed
	 */
	function get($key, &$exists = null) {
		if (($exists = array_key_exists($key, static::$cache))) {
			list($ttl, $data) = static::$cache[$key];
			if ($ttl && $ttl < time()) {
				unset(static::$cache[$key]);
				$exists = false;
				return null;
			}
			return $data;
		}
	}
	
	/**
	 * @inheritdoc
	 * @param string $key
	 * @param mixed $val
	 * @param int $ttl
	 * @return \pq\Gateway\Table\StaticCache
	 */
	function set($key, $val, $ttl = 0) {
		static::$cache[$key] = array(
			$ttl ? $ttl + time() : 0,
			$val
		);
		return $this;
	}
	
	/**
	 * @inheritdoc
	 * @param string $key
	 * @return \pq\Gateway\Table\StaticCache
	 */
	function del($key) {
		unset(static::$cache[$key]);
		return $this;
	}
}
