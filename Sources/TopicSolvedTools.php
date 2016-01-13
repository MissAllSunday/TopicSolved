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
	protected $_statusFields = array('status_id', 'name', 'color', 'css', 'icon', 'enable', 'is_solved');
	protected $_topicStatus = array(
		'notsolved' => 'fa-exclamation-triangle',
		'solved' => 'fa-check-square-o',
	);

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

	public function checkPermissions()
	{
		global $user_info;

		if (!allowedTo($this->name .'_any') && $user_info['id'] == $this->topicInfo['id_member_started'])
			isAllowedTo($this->name .'_own');

		else
			isAllowedTo($this->name .'_any');
	}

	public function getStatus()
	{
		$raw = $this->setting('status');
		$status = array();

		if (empty($raw))
			return $status;

		// The first item its the key!
		foreach (explode('\n', $raw) as $r)
		{
			$r = explode('|', $r);
			$status[$r[0]] = array_shift($r);
		}

		return $status;
	}
}