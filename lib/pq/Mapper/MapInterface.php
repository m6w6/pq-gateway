<?php

namespace pq\Mapper;

use pq\Gateway\Row;
use pq\Gateway\Rowset;
use pq\Gateway\Table;

interface MapInterface
{
	/**
	 * @return string
	 */
	function getClass();

	/**
	 * @return Table
	 */
	function getGateway();

	/**
	 * @return array of PropertyInterface instances
	 */
	function getProperties();

	/**
	 * @param PropertyInterface $property
	 */
	function addProperty(PropertyInterface $property);

	/**
	 * @param Row $row
	 * @return object
	 */
	function map(Row $row);

	/**
	 * @param \pq\Mapper\Rowset $rows
	 * @return array
	 */
	function mapAll(Rowset $rows);

	/**
	 * @param object $object
	 * @return Row
	 */
	function unmap($object);
}