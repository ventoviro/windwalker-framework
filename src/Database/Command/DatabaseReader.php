<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Database\Command;

use Windwalker\Database\DataIterator;
use Windwalker\Database\Driver\DatabaseDriver;
use Windwalker\Database\Driver\DatabseAwareTrait;
use Windwalker\Query\Query;

/**
 * Class DatabaseReader
 *
 * @since 1.0
 */
abstract class DatabaseReader
{
	use DatabseAwareTrait;

	/**
	 * Property resource.
	 *
	 * @var  resource
	 */
	protected $resource = null;

	/**
	 * setDb
	 *
	 * @param   DatabaseDriver $db
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDb($db)
	{
		$this->db = $db;

		$this->resource = $this->db->getCursor();

		return $this;
	}

	/**
	 * setQuery
	 *
	 * @param Query $query
	 *
	 * @return  $this
	 */
	public function setQuery($query)
	{
		$this->db->setQuery($query);

		return $this;
	}

	/**
	 * getIterator
	 *
	 * @param string $class
	 *
	 * @return  DataIterator
	 */
	public function getIterator($class = '\\stdClass')
	{
		$this->db->execute();

		return new DataIterator($this, $class);
	}

	/**
	 * Method to get the first field of the first row of the result set from the database query.
	 *
	 * @return  mixed  The return value or null if the query failed.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function loadResult()
	{
		$this->db->execute();

		// Get the first row from the result set as an array.
		$row = $this->fetchArray();

		if ($row)
		{
			$row = $row[0];
		}

		// Free up system resources and return.
		$this->freeResult();

		return $row;
	}

	/**
	 * Method to get an array of values from the <var>$offset</var> field in each row of the result set from
	 * the database query.
	 *
	 * @param   integer  $offset  The row offset to use to build the result array.
	 *
	 * @return  mixed  The return value or null if the query failed.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function loadColumn($offset = 0)
	{
		$this->db->execute();

		$array = array();

		// Get all of the rows from the result set as arrays.
		while ($row = $this->fetchArray())
		{
			$array[] = $row[$offset];
		}

		// Free up system resources and return.
		$this->freeResult();

		return $array;
	}

	/**
	 * Method to get the first row of the result set from the database query as an array.  Columns are indexed
	 * numerically so the first column in the result set would be accessible via <var>$row[0]</var>, etc.
	 *
	 * @return  mixed  The return value or null if the query failed.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function loadArray()
	{
		$this->db->execute();

		// Get the first row from the result set as an array.
		$array = $this->fetchArray();

		// Free up system resources and return.
		$this->freeResult();

		return $array;
	}

	/**
	 * Method to get an array of the result set rows from the database query where each row is an array.  The array
	 * of objects can optionally be keyed by a field offset, but defaults to a sequential numeric array.
	 *
	 * NOTE: Choosing to key the result array by a non-unique field can result in unwanted
	 * behavior and should be avoided.
	 *
	 * @param   string  $key  The name of a field on which to key the result array.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function loadArrayList($key = null)
	{
		$this->db->execute();

		$array = array();

		// Get all of the rows from the result set as arrays.
		while ($row = $this->fetchArray())
		{
			if ($key !== null)
			{
				$array[$row[$key]] = $row;
			}
			else
			{
				$array[] = $row;
			}
		}

		// Free up system resources and return.
		$this->freeResult();

		return $array;
	}

	/**
	 * Method to get the first row of the result set from the database query as an associative array
	 * of ['field' => 'value'].
	 *
	 * @return  mixed  The return value or null if the query failed.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function loadAssoc()
	{
		$this->db->execute();

		// Get the first row from the result set as an associative array.
		$array = $this->fetchAssoc();

		// Free up system resources and return.
		$this->freeResult();

		return $array;
	}

	/**
	 * Method to get an array of the result set rows from the database query where each row is an associative array
	 * of ['field_name' => 'row_value'].  The array of rows can optionally be keyed by a field name, but defaults to
	 * a sequential numeric array.
	 *
	 *
	 * @param   string  $key  The name of a field on which to key the result array.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function loadAssocList($key = null)
	{
		$this->db->execute();

		$array = array();

		// Get all of the rows from the result set.
		while ($row = $this->fetchAssoc())
		{
			if ($key)
			{
				$array[$row[$key]] = $row;
			}
			else
			{
				$array[] = $row;
			}
		}

		// Free up system resources and return.
		$this->freeResult();

		return $array;
	}

	/**
	 * Method to get the first row of the result set from the database query as an object.
	 *
	 * @param   string  $class  The class name to use for the returned row object.
	 *
	 * @return  mixed  The return value or null if the query failed.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function loadObject($class = 'stdClass')
	{
		$this->db->execute();

		// Get the first row from the result set as an object of type $class.
		$object = $this->fetchObject($class);

		// Free up system resources and return.
		$this->freeResult();

		return $object;
	}

	/**
	 * Method to get an array of the result set rows from the database query where each row is an object.  The array
	 * of objects can optionally be keyed by a field name, but defaults to a sequential numeric array.
	 *
	 * NOTE: Choosing to key the result array by a non-unique field name can result in unwanted
	 * behavior and should be avoided.
	 *
	 * @param   string  $key    The name of a field on which to key the result array.
	 * @param   string  $class  The class name to use for the returned row objects.
	 *
	 * @return  mixed  The return value or null if the query failed.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function loadObjectList($key = null, $class = 'stdClass')
	{
		$this->db->execute();

		$array = array();

		// Get all of the rows from the result set as objects of type $class.
		while ($row = $this->fetchObject($class))
		{
			if ($key)
			{
				$array[$row->$key] = $row;
			}
			else
			{
				$array[] = $row;
			}
		}

		// Free up system resources and return.
		$this->freeResult();

		return $array;
	}

	abstract public function count();

	/**
	 * Method to fetch a row from the result set cursor as an array.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   1.0
	 */
	abstract public function fetchArray();

	/**
	 * Method to fetch a row from the result set cursor as an associative array.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   1.0
	 */
	abstract public function fetchAssoc();

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @param   string  $class  Unused, only necessary so method signature will be the same as parent.
	 *
	 * @return  mixed   Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   1.0
	 */
	abstract public function fetchObject($class = '\\stdClass');

	/**
	 * freeResult
	 *
	 * @return  $this
	 */
	public function freeResult()
	{
		$this->db->freeResult();

		return $this;
	}
}
 