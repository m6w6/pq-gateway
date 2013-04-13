<?php

namespace pq\Gateway\Table;

use \pq\Gateway\Row;

/**
 * A pessimistic row lock implementation using an additional SELECT FOR UPDATE
 */
class PessimisticLock implements LockInterface
{
	/**
	 * @inheritdoc
	 * @param \pq\Gateway\Row $row
	 * @param array $ignore
	 * @throws \UnexpectedValueException if the row has already been modified
	 */
	function onUpdate(Row $row, array &$ignore) {
		$where = array();
		foreach ($row->getIdentity() as $col => $val) {
			if (isset($val)) {
				$where["$col="] = $val;
			} else {
				$where["$col IS"] = new QueryExpr("NULL");
			}
		}
		
		if (1 != count($rowset = $row->getTable()->find($where, null, 0, 0, "update nowait"))) {
			throw new \UnexpectedValueException("Failed to select a single row");
		}
		if ($rowset->current()->getData() != $row->getData()) {
			throw new \UnexpectedValueException("Row has already been modified");
		}
	}
}