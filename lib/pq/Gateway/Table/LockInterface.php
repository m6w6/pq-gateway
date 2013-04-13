<?php

namespace pq\Gateway\Table;

use \pq\Gateway\Row;

/**
 * Lazy row lock on update/delete
 */
interface LockInterface
{
	function onUpdate(Row $row, array &$where);
}
