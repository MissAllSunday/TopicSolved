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
	die('No direct access!');

class TopicSolvedTools extends Suki\Ohara
{
	// Cheating!
	public $name = 'TopicSolved';

	// Define the hooks we are going to use.
	protected $_availableHooks = array(
		'actions' => 'integrate_actions',
		'displayTopic' => 'integrate_display_topic',
		'displayButtons' => 'integrate_display_buttons',
		'messageIndex' => 'integrate_message_index',
		'messageButtons' => 'integrate_messageindex_buttons',
		'helpAdmin' => 'integrate_helpadmin',
		'adminArea' => 'integrate_admin_areas',
		'permissions' => 'integrate_load_permissions',
		'log' => 'integrate_manage_logs',
	);

	// Tell SMF where the settings are!
	protected $_overwriteHooks = array(
		'adminArea' => array(
			'func' => 'TopicSolvedAdmin::addAdminArea',
			'file' => 'TopicSolvedAdmin.php',
		),
		'permissions' => array(
			'func' => 'TopicSolvedAdmin::addPermissions',
			'file' => 'TopicSolvedAdmin.php',
		),
	);

/* 	Basic 3 stages:
	0 means normal topics IE empty class
	1 means solved topics
	2 means topics not solved */
	protected $_statusFields = array('', 'notsolved', 'solved');

	protected $_dbField = 't.is_solved';

	public function __construct()
	{
		$this->setRegistry();
	}

	public function getTopicInfo($topic = false)
	{
		global $smcFunc;

		if (empty($topic) )
			return false;

		// Work with arrays.
		$topic = (array) $topic;

		$request = $smcFunc['db_query']('', '
			SELECT id_topic, id_member_started, id_first_msg, id_last_msg, is_solved
			FROM {db_prefix}topics
			WHERE id_topic IN({array_int:topic})',
			array(
				'topic' => array_unique($topic),
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($query))
			$this->_topicInfo[$topic['id_topic']] = $smcFunc['db_fetch_assoc']($request);

		$smcFunc['db_free_result']($result);

		return $this->topicInfo;
	}

	public function changeStatus($data = array())
	{
		global $smcFunc;

		if (empty($data))
			return false;

		// Work with arrays.
		$data['topic'] = (array) $data['topic'];

		// Make the change.
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}topics
			SET is_solved = {int:is_solved}
			WHERE id_topic IN({array_int:topic})',
			$data
		);
	}

	public function getTopicLogCount()
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}topic_solved_log',
			array()
		);
		list ($totalLogs) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		return (int) $totalLogs;
	}

	public function getTopicLogs()
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}topic_solved_log',
			array()
		);
		list ($totalLogs) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		return (int) $totalLogs;
	}

	public function checkPermissions($topicOwner = 0)
	{
		global $user_info;

		if (!allowedTo($this->name .'_any') && $user_info['id'] == $topicOwner)
			return allowedTo($this->name .'_own');

		else
			return allowedTo($this->name .'_any');
	}

	/* Checks if the mod is enable and if the current board is a selected one. */
	public function innerCheck()
	{
		global $board, $context;

		// Perhaps someone else wants to disable this mod for whatever reason!
		if (!empty($context['force_disable_'. $this->name]))
			return false;

		// Mod's gotta be enable and a board needs to be selected.
		if (!$this->enable('master') || !$this->enable('boards') || empty($board))
			return false;

		// A board needs to be selected.
		$tBoards = explode(',', $this->setting('boards'));

		return in_array($board, $tBoards);
	}

	public function getStatus()
	{
		$raw = $this->setting('status');
		$status = array();

		if (empty($raw))
			return $status;

		// The first item its the key!
		foreach (explode(PHP_EOL, $raw) as $r)
		{
			$r = explode('|', $r);
			$status[$r[0]] = $r[1];
		}

		return $status;
	}
}