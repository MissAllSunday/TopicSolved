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

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

else if(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php and SSI.php files.');

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin privileges required.');

	if (empty($context['uninstalling']))
	{
		$tables[] = array(
			'table_name' => '{db_prefix}topic_solved',
			'columns' => array(
				array(
					'name' => 'status_id',
					'type' => 'int',
					'size' => 5,
					'null' => false,
					'auto' => true
				),
				array(
					'name' => 'name',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'color',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'css',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'icon',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'enable',
					'type' => 'int',
					'size' => 1,
					'null' => false
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('status_id')
				),
			),
			'if_exists' => 'ignore',
			'error' => 'fatal',
			'parameters' => array(),
		);

		// Installing
		foreach ($tables as $table)
			$smcFunc['db_create_table']($table['table_name'], $table['columns'], $table['indexes'], $table['parameters'], $table['if_exists'], $table['error']);

		// Add some basic topic status.
		$status = array(
			'solved' => array(
				'name' => 'Solved',
				'color' => '008000',
				'css' => 'solved',
				'icon' => 'fa-check-square-o',
			),
			'not_solved' => array(
				'name' => 'Not Solved',
				'color' => 'FF1919',
				'css' => 'not_solved',
				'icon' => 'fa-exclamation-triangle',
			),
			'pending' => array(
				'name' => 'Pending',
				'color' => 'FFCC00',
				'css' => 'pending',
				'icon' => 'fa-exclamation-circle',
			),
		);

		foreach ($status as $s)
			$smcFunc['db_insert']('replace', '{db_prefix}topic_solved', array(
					'name' => 'string',
					'color' => 'string',
					'css' => 'string',
					'icon' => 'string',
					'enable' => 'int',
				), array(
					$s['name'],
					$s['color'],
					$s['css'],
					$s['icon'],
					1
				), array('status_id',)
			);

		// Create the scheduled task.
		$smcFunc['db_insert'](
			'insert',
			'{db_prefix}scheduled_tasks',
			array(
				'id_task' => 'int',
				'next_time' => 'int',
				'time_offset' => 'int',
				'time_regularity' => 'int',
				'time_unit' => 'string',
				'disabled' => 'int',
				'task' => 'string',
				'callable' => 'string',
			),
			array(
				0, 0, 0, 1, 'd', 0, 'topic_solved', '$sourcedir/TopicSolved.php|TopicSolved::task',
			),
			array(
				'id_task',
			)
		);

		db_extend('packages');

		// Add the column.
		$smcFunc['db_add_column'](
			'{db_prefix}topics',
			array(
				'name' => 'is_solved',
				'type' => 'int',
				'size' => 2,
				'null' => false,
				'default' => 0,
				'unsigned' => true,
			),
			array(),
			'update',
			null
		);
	}

if (SMF == 'SSI')
	echo 'Database changes are complete!';
