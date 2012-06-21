<?php

/**
 * @package Topic Solved mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2012 Suki
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

/*
 * Version: MPL 2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 * If a copy of the MPL was not distributed with this file, 
 * You can obtain one at http://mozilla.org/MPL/2.0/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class TopicSolvedDB
{
	protected $_table;
	protected $_dataResult = array();
	protected $_data = array();
	protected $_key;

	function __construct($table)
	{
		$this->_table = isset($table) ? '{db_prefix}'.$table : null;
	}

	function params($params, $data = null, $values = null)
	{
		if(is_null($params))
			return false;

		$this->_rows = isset($params['rows']) ? trim($params['rows']) : null;
		$this->_where = isset($params['where']) ? 'WHERE '.trim($params['where']) : null;
		$this->_whereAnd = isset($params['and']) ? 'AND '.trim($params['and']) : null;
		$this->_limit = isset($params['limit']) ? 'LIMIT '.trim($params['limit']) : null;
		$this->_left = isset($params['left_join']) ? 'LEFT JOIN '.trim($params['left_join']) : null;
		$this->_order = isset($params['order']) ? 'ORDER BY '.trim($params['order']) : null;
		$this->_set = isset($params['set']) ? 'SET '.trim($params['set']) : null;
		$this->_data = !is_array($data) ? array($data) : $data;
	}

	function getData($key = null, $single = false)
	{
		global $smcFunc;

		if ($key)
			$this->key = $key;

		$query = $smcFunc['db_query']('', '
			SELECT '.$this->_rows .'
			FROM '.$this->_table .'
			'. $this->_left .'
			'. $this->_where .'
			'. $this->_whereAnd .'
			'. $this->_order .'
			'. $this->_limit .'
			',
			$this->_data
		);

		if (!$query)
			$this->_dataResult = array();

		if($single)
			while ($row = $smcFunc['db_fetch_assoc']($query))
				$this->_dataResult = $row;

		if ($key)
			while($row = $smcFunc['db_fetch_assoc']($query))
				$this->_dataResult[$row[$this->key]] = $row;

		else
			while($row = $smcFunc['db_fetch_assoc']($query))
				$this->_dataResult[] = $row;

		$smcFunc['db_free_result']($query);
	}

	function dataResult()
	{
		return $this->_dataResult;
	}

	function updateData()
	{
		global $smcFunc;

		$smcFunc['db_query']('', '
			UPDATE '.$this->_table .'
			'.$this->_set .'
			'.$this->_where .'
			'.$this->_order .'
			'.$this->_limit .'
			',
			$this->_data
		);
	}

	function deleteData()
	{
		global $smcFunc;

		$smcFunc['db_query']('', '
			DELETE FROM '.$this->_table .'
			'.$this->_where .'
			'.$this->_order .'
			'.$this->_limit .'
			',
			$this->_data
		);
	}

	function insertData($data, $values, $indexes)
	{
		if(is_null($values) || is_null($indexes) || is_null($data))
			return false;

		global $smcFunc;

		$this->indexes = isset($params['indexes']) ? array($params['indexes']) : null;
		$this->values = !is_array($values) ? array($values) : $values;
		$this->_data = !is_array($data) ? array($data) : $data;

		$smcFunc['db_insert']('replace',
			''.$this->_table .'',
			$this->_data ,
			$this->values ,
			$this->indexes
		);
	}

	function count($params = null, $data = null)
	{
		global $smcFunc;

		if(is_null($params))
			$params = array();

		if(is_null($data))
			$data = array();

		$this->_data = !is_array($data) ? array($data) : $data;
		$this->_where = isset($params['where']) ? 'WHERE '.trim($params['where']) : null;
		$this->_left = isset($params['left_join']) ? 'LEFT JOIN '.trim($params['left_join']) : null;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM '.$this->_table .'
			' . $this->_where . '
			' . $this->_left . '
			',
			$this->_data
		);

		list ($count) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		return $count;
	}
}