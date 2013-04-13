<?php

namespace pq\Gateway\Table;

use \pq\Gateway\Row;

interface LockInterface
{
	function criteria(Row $row, array &$where);
}
