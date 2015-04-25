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

global $txt, $scripturl;

$txt['TopicSolved_menuTitle'] = 'Topic Solved';
$txt['TopicSolved_settingsGeneral'] = 'General';
$txt['TopicSolved_menuDesc'] = 'You can configure the Topic Solved mod here.';
$txt['TopicSolved_enable'] = 'Enable the Topic Solved mod';
$txt['TopicSolved_enable_sub'] = 'This is the master setting, must be enable for the mod to work properly';
$txt['TopicSolved_boards'] = 'The Board IDs where the mod will work';
$txt['TopicSolved_boards_sub'] = 'Comma separated, example: 1,2,3,4';
$txt['TopicSolved_selectGroups'] = 'Select the groups that will act as "staff".';
$txt['TopicSolved_selectGroups_sub'] = 'Users within any group marked as "staff" will be used to reference when a topic can be marked as solved';
$txt['TopicSolved_lockTopic'] = 'Lock the topic when marked as solved';
$txt['TopicSolved_lockTopic_sub'] = 'Whenever a topic is marked as solved, the topic will also be locked.';
$txt['TopicSolved_not_a_topic'] = 'The topic doesn\'t exists.';
$txt['TopicSolved_topic_solved_no_board'] = 'Sorry, topic solved feature is not enabled in this board.';
$txt['TopicSolved_staffRespond'] = 'Enable checking staff responses';
$txt['TopicSolved_staffRespond_sub'] = 'If enable, the topics where a staff member hasn\'t answered will be marked with the status you chose';
$txt['TopicSolved_staffRespondStatus'] = 'Chose the status to mark those topics that haven\'t been replied by a staff member';
$txt['TopicSolved_staffRespondStatus_sub'] = 'When the scheduled task gets executed, any topic matching the criteria will be changed to this status.';
$txt['TopicSolved_daysNotResponded'] = 'How many days need to have passed before checking for a staff response?';
$txt['TopicSolved_daysNotResponded_sub'] = 'As in, how many days a topic needs to stay without a reply form a staff member to be marked.';
$txt['TopicSolved_'] = '';
$txt['TopicSolved_'] = '';
$txt['TopicSolved_'] = '';
$txt['TopicSolved_'] = '';
$txt['TopicSolved_'] = '';
$txt['TopicSolved_'] = '';
$txt['TopicSolved_'] = '';
$txt['TopicSolved_'] = '';

/* Permissions */
$txt['permissiongroup_simple_topicsolved_per_simple'] = 'Breeze mod permissions';
$txt['permissiongroup_topicsolved_per_classic'] = 'Breeze mod permissions';
$txt['permissionname_solve_topic'] = 'Marks topics solved';
$txt['permissionhelp_solve_topic'] = 'This permission allows a user to a topic solved.';
$txt['permissionname_solve_topic_own'] = 'Own topic';
$txt['permissionname_solve_topic_any'] = 'Any topic';
$txt['permissionname_simple_solve_topic_own'] = 'Mark their own topic as solved';
$txt['permissionname_simple_solve_topic_any'] = 'Mark anyone\'s topic as solved';