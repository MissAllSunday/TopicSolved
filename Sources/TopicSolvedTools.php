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
	public $logType = 69;

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
		'logType' => 'integrate_log_types',
	);

	// Tell SMF where the settings are!
	protected $_overwriteHooks = array(
		'adminArea' => array(
			'func' => 'TopicSolvedAdmin::addAdminArea#',
			'file' => 'TopicSolvedAdmin.php',
		),
		'permissions' => array(
			'func' => 'TopicSolvedAdmin::addPermissions#',
			'file' => 'TopicSolvedAdmin.php',
		),
	);

/* 	Basic 3 stages:
	0 means normal topics, IE empty class
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
			FROM {db_prefix}log_actions
			WHERE id_log = {int:log_type}',
			array('log_type' => $this->logType)
		);
		list ($totalLogs) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		return (int) $totalLogs;
	}

	public function getTopicLogs($start, $items_per_page)
	{
		global $smcFunc, $txt;

		$entries = array();

		$result = $smcFunc['db_query']('', '
			SELECT
				lm.id_action, lm.id_member, lm.ip, lm.log_time, lm.action, lm.id_topic, lm.id_msg, lm.extra, t.id_first_msg, m.subject,
				mem.real_name, mg.group_name
			FROM {db_prefix}log_actions AS lm
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lm.id_member)
				LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN mem.id_group = {int:reg_group_id} THEN mem.id_post_group ELSE mem.id_group END)
				LEFT JOIN {db_prefix}topics AS t ON (t.id_topic = lm.id_topic)
				LEFT JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
				WHERE id_log = {int:log_type}
			ORDER BY lm.id_action DESC
			LIMIT ' . $start . ', ' . $items_per_page,
			array('log_type' => $this->logType, 'reg_group_id' => 0,)
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			if (!empty($row['extra']))
				$row['extra'] = json_decode($row['extra'], true);

			$entries[$row['id_action']] = array(
				'id' => $row['id_action'],
				'position' => empty($row['real_name']) && empty($row['group_name']) ? $txt['guest'] : $row['group_name'],
				'moderator_link' => $row['id_member'] ? '<a href="' . $this->scriptUrl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>' : (empty($row['real_name']) ? ($txt['guest'] . (!empty($row['extra']['member_acted']) ? ' (' . $row['extra']['member_acted'] . ')' : '')) : $row['real_name']),
				'time' => timeformat($row['log_time']),
				'timestamp' => forum_time(true, $row['log_time']),
				'extra' => $row['extra'],
				'action' => $row['action'],
				'subject' => $row['subject'],
				'href' => $this->scriptUrl . '?msg=' . $row['id_msg'],
				'link' => '<a href="' . $this->scriptUrl . '?topic='. $row['id_topic'] .'.0">' . $row['subject'] . '</a>',
				'is_solved' => (!empty($row['extra']['is_solved']) ? $this->_statusFields[$row['extra']['is_solved']] : '')
			);
		}

		$smcFunc['db_free_result']($result);

		return $entries;
	}

	public function deleteTopicLogs($data = array())
	{
		global $smcFunc;

		// Ane mepty $data var means all entries will be deleted!
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_actions
			WHERE id_log = {int:log_type}
			'. (!empty($data) ? '
				AND id_action IN ({array_int:data})' : '') .'',
			array('log_type' => $this->logType, 'data' => $data)
		);
	}

	public function lockTopic($topicID = array(), $lock = 0)
	{
		global $smcFunc;

		if (empty($topicID))
			return false;

		// Work with arrays.
		$topicID = (array) $topicID;

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}topics
			SET locked = {int:locked}
			WHERE id_topic IN({array_int:topic})',
			array(
				'locked' => $lock,
				'topic' => $topicID,
			)
		);
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

	protected function getGroups()
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT id_group, group_name
			FROM {db_prefix}membergroups',
			array()
		);
		$return = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$return[$row['id_group']] = $row['group_name'];

		$smcFunc['db_free_result']($request);

		return $return;
	}
}