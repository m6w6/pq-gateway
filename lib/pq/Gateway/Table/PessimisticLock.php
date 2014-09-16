<?php

namespace pq\Gateway\Table;

use \pq\Gateway\Row;

/**
 * A pessimistic row lock implementation using an additional SELECT FOR UPDATE
 */
class PessimisticLock implements \SplObserver
{
	/**
	 * @param \pq\Gateway\Table $table
	 * @param \pq\Gateway\Row $row
	 * @param string $event create/update/delete
	 * @param array $where reference to the criteria
	 * @throws \UnexpectedValueException if the row has already been modified
	 */
	function update(\SplSubject $table, Row $row = null, $event = null, array &$where = null) {
		if ($event === "update") {
			if (1 != count($rowset = $table->find($where, null, 0, 0, "update nowait"))) {
				throw new \UnexpectedValueException("Failed to select a single row");
			}
			if ($rowset->current()->getData() != $row->getData()) {
				throw new \UnexpectedValueException("Row has already been modified");
			}
		}
	}
}