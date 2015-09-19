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
	 * @param string $refName
	 * @param array $objects
	 * @return Rowset
	 */
	function allOf(Row $row, $refName, &$objects = null);

	/**
	 * @param Row $row
	 * @param string $refName
	 * @param array $objects
	 * @return Rowset
	 */
	function refOf(Row $row, $refName, &$objects = null);

	/**
	 * @param MapInterface $map origin
	 * @param string $refName relations reference name
	 * @return array relation reference
	 */
	function relOf(MapInterface $map, $refName);

	/**
	 * @param Row $row
	 * @return object
	 */
	function map(Row $row);

	/**
	 * @param Rowset $rows
	 * @return array
	 */
	function mapAll(Rowset $rows);

	/**
	 * @param object $object
	 * @return Row
	 */
	function unmap($object);
}
