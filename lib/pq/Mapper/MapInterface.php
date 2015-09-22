<?php

namespace pq\Mapper;

use pq\Gateway\Row;
use pq\Gateway\Rowset;
use pq\Gateway\Table;

interface MapInterface
{
	/**
	 * Get the mapped class' name
	 * @return string
	 */
	function getClass();

	/**
	 * Get the object manager
	 * @return ObjectManager
	 */
	function getObjects();

	/**
	 * The the underlying table gateway
	 * @return Table
	 */
	function getGateway();

	/**
	 * Get the mapped properties
	 * @return PropertyInterface[]
	 */
	function getProperties();

	/**
	 * Add a property to map
	 * @param PropertyInterface $property
	 */
	function addProperty(PropertyInterface $property);

	/**
	 * Get all child rows by foreign key
	 * @param Row $row
	 * @param string $refName
	 * @param array $objects
	 * @return Rowset
	 */
	function allOf(Row $row, $refName, &$objects = null);

	/**
	 * Get the parent row by foreign key
	 * @param Row $row
	 * @param string $refName
	 * @param array $objects
	 * @return Rowset
	 */
	function refOf(Row $row, $refName, &$objects = null);

	/**
	 * Get the table relation reference
	 * @param MapInterface $map origin
	 * @param string $refName relations reference name
	 * @return Table\Reference
	 */
	function relOf(MapInterface $map, $refName);

	/**
	 * Map a row to an object
	 * @param Row $row
	 * @return object
	 */
	function map(Row $row);

	/**
	 * Map a rowset to an array of objects
	 * @param Rowset $rows
	 * @return object[]
	 */
	function mapAll(Rowset $rows);

	/**
	 * Unmap on object
	 * @param object $object
	 * @return Row
	 */
	function unmap($object);
}
