<?php

namespace pq\Gateway\Table;

use \pq\Gateway\Row;

/**
 * Lazy row lock on update
 */
interface LockInterface
{
	function onUpdate(Row $row, array &$where);
}
