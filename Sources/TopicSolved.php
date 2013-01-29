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


function getTopicStatus($topic)
{
	global $smcFunc;

	if empty($topic) || is_numeric((int) $topic)
		return false;

	/* Cache is empty, get the info */
	if (($return = cache_get_data('TopicSolved:' . $topic, 120)) == null)
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_member_started, id_first_msg, id_last_msg, is_solved
			FROM {db_prefix}topics
			WHERE id_topic = {int:topic}
			LIMIT {int:limit}',
			array(
				'topic' => $topic,
				'limit' => 1,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
			$return = array(
				'id_member_started' => $row['id_member_started'],
				'id_first_msg' => $row['id_first_msg'],
				'id_last_msg' => $row['id_last_msg'],
				'is_solved' => $row['is_solved'],
			);

		$smcFunc['db_free_result']($result);

		/* Cache this beauty */
		cache_put_data('TopicSolved:' . $topic, $return, 120);
	}

	return $return;
}