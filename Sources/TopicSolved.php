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

class TopicSolved
{
	protected static $name = 'TopicSolved';
	protected $topic = 0;
	protected $topicInfo = array();

	public function __construct($topic = 0)
	{
		if (!empty($topic)
			$this->topic = (int) $topic;
	}

	public static function call()
	{
		checkSession('get');

		/* Get the topic ID */
		$temp = !empty($_GET['topic']) && is_numeric($_GET['topic']) ? (int) trim($_GET['topic']) : 0;

		$topic = new self($temp);
		$topic->changeStatus();
	}

	protected function getTopicStatus($topic = null)
	{
		global $smcFunc;

		/* Overwrite the global $topic */
		if !empty($topic) || is_numeric((int) $topic)
			$this->topic = (int) $topic;

		/* Cache is empty, get the info */
		if (($this->topicInfo = cache_get_data(self::$name .'-' . $topic, 120)) == null)
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_member_started, id_first_msg, id_last_msg, is_solved
				FROM {db_prefix}topics
				WHERE id_topic = {int:topic}
				LIMIT {int:limit}',
				array(
					'topic' => $this->topic,
					'limit' => 1,
				)
			);

			$this->topicInfo = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($result);

			/* Cache this beauty */
			cache_put_data(self::$name .'-' . $topic, $this->topicInfo, 120);
		}

		return $this->topicInfo;
	}

	public function changeStatus($topic = null, $status = null)
	{
		global $smcFunc;

		$topic = empty($topic) ? $this->topic ? $topic;

		if (empty($status))
			return false;

		/* Apply permissions */
		$this->checkPermissions();

		/* Is $topic an array?  */
		if (!empty($topic) && is_array($topic))
		{
			/* By default sets an empty array */
			$array = array();

			foreach ($topic as $t)
				$array[] = $this->getTopicStatus($t);


		}

		else if (!empty($topic) && is_numeric($topic))
		{
			$this->getTopicStatus();

			/* Make the change */
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}topics
				SET is_solved = {int:is_solved}
				WHERE id_topic = {int:topic}
				LIMIT {int:limit}',
				array(
					'topic' => $topic,
					'is_solved' => empty($row['is_solved']) ? 1 : 0,
					'limit' => 1,
				)
			);
		}

		/* Its all good */
	}

	protected function checkPermissions()
	{
		global $user_info;

		if (!allowedTo('solve_topic_any') && $user_info['id'] == $this->topicInfo['id_member_started'])
			isAllowedTo('solve_topic_own');

		else
			isAllowedTo('solve_topic_any');
	}

	public static function permissions($permissionGroups, $permissionList)
	{
		$permissionGroups['membergroup']['simple'] = array('topicsolved_per_simple');
		$permissionGroups['membergroup']['classic'] = array('topicsolved_per_classic');
		$permissionList['membergroup']['solve_topic_own'] = array(
			false,
			'topicsolved_per_classic',
			'topicsolved_per_simple');
		$permissionList['membergroup']['solve_topic_any'] = array(
			false,
			'topicsolved_per_classic',
			'topicsolved_per_simple');
	}
}