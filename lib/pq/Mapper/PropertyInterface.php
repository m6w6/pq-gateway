<?php

namespace pq\Mapper;

use pq\Gateway\Row;

interface PropertyInterface
{
	/**
	 * Write the value for the property from $object into the row to update
	 * @param object $object
	 * @param Row $rowToUpdate
	 * @return null|callable eventual deferred callback
	 */
	function write($object, Row $rowToUpdate);

	/**
	 * Read the value for the property from $row into the mapped object
	 * @param Row $row
	 * @param object $objectToUpdate
	 * @return null|callable eventual deferred callback
	 */
	function read(Row $row, $objectToUpdate);

	/**
	 * Set the value of the mapped property
	 * @param object $object
	 * @param mixed $value
	 */
	function assign($object, $value);

	/**
	 * Get the value of the mapped property
	 * @param object $object
	 * @return mixed
	 */
	function extract($object);
	
	/**
	 * Get the property name
	 * @return string
	 */
	function getProperty();

	/**
	 * Get the containing map
	 * @return MapInterface
	 */
	function getContainer();

	/**
	 * Set the containing map
	 * @param MapInterface $container
	 * @return Property
	 */
	function setContainer(MapInterface $container);
	
	/**
	 * Check whether this Property defines $property
	 * @param string $property
	 * @return bool
	 */
	function defines($property);

	/**
	 * Check whether this property exposes $field
	 * @param string $field
	 * @return bool
	 */
	function exposes($field);
}
