<?php

/**
 * @package Topic Solved mod
 * @version 1.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright 2016 Suki
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

else if(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php and SSI.php files.');

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin privileges required.');

	global $smcFunc, $context;

	db_extend('packages');

	// Add the is_solved column.
	if (empty($context['uninstalling']))
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

	// Create the scheduled task.
	if (empty($context['uninstalling']))
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
				0, 0, 0, 1, 'd', 0, 'TopicSolved', 'TopicSolved.php|TopicSolved::scheduledTask#',
			),
			array(
				'id_task',
			)
		);

if (SMF == 'SSI')
	echo 'Database changes are complete!';
