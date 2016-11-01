<?php

/**
 * @package Topic Solved mod
 * @version 1.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright 2016 Suki
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

elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $smcFunc;

db_extend('packages');

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}scheduled_tasks
	WHERE task = {string:name}',
	array(
		'name' => 'topicSolved',
	)
);