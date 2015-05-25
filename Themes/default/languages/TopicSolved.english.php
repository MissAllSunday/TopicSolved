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

$txt['TopicSolved_modCredits'] = '<a href="http://missallsunday.com" title="Free SMF Mods">Topic Solved mod &copy Suki</a>';
$txt['TopicSolved_modName'] = 'Topic Solved';
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
$txt['TopicSolved_lockTopic_op'] = 'The OP';
$txt['TopicSolved_lockTopic_staff'] = 'Staff';
$txt['TopicSolved_lockTopic_both'] = 'Staff or OP';
$txt['TopicSolved_lockTopic_automatic'] = 'Automatic';
$txt['TopicSolved_lockTopic_always'] = 'Always';
$txt['TopicSolved_lockTopicWhen'] = 'Lock the topic when marked by:';
$txt['TopicSolved_lockTopicWhen_sub'] = 'Chose which user can trigger locking a topic when marked as solved, options are:
<br>- '. $txt['TopicSolved_lockTopic_op'] .'. The topic will be locked only if the Original Poster marked the topic as solved
<br>- '. $txt['TopicSolved_lockTopic_staff'] .'. The topic will be locked only if someone marked as staff mark the topic as solved.
<br>- '. $txt['TopicSolved_lockTopic_both'] .'. The topic will be locked if the OP or someone marked as staff marks the topic as solved.
<br>- '. $txt['TopicSolved_lockTopic_automatic'] .'. The topic will be locked only when the scheduled task is executed.
<br>- '. $txt['TopicSolved_lockTopic_always'] .'. The topic will be locked whenever it gets marked as solved regardless of who marke it.';
$txt['TopicSolved_lockTopic'] = 'Lock the topic when marked as solved';
$txt['TopicSolved_lockTopic_sub'] = 'Whenever a topic is marked as solved, the topic will also be locked.';
$txt['TopicSolved_not_a_topic'] = 'The topic doesn\'t exists.';
$txt['TopicSolved_topic_solved_no_board'] = 'Sorry, topic solved feature is not enabled in this board.';
$txt['TopicSolved_staffRespond'] = 'Enable checking staff responses';
$txt['TopicSolved_staffRespond_sub'] = 'If enable, the topics where a staff member hasn\'t answered will be marked with the status you chose';
$txt['TopicSolved_staffRespondStatus'] = 'Chose the status to mark those topics that haven\'t been replied by a staff member';
$txt['TopicSolved_staffRespondStatus_sub'] = 'When the scheduled task gets executed, any topic matching the criteria will be changed to this status.';
$txt['TopicSolved_daysNotResponded'] = 'How many days need to have passed before changing a topic status?';
$txt['TopicSolved_daysNotResponded_sub'] = 'As in, how many days a topic needs to stay without a reply form a staff member or the OP to change its status.';
$txt['TopicSolved_'] = '';
$txt['TopicSolved_'] = '';
$txt['TopicSolved_'] = '';
$txt['TopicSolved_'] = '';
$txt['TopicSolved_'] = '';
$txt['TopicSolved_'] = '';
$txt['TopicSolved_'] = '';
$txt['TopicSolved_'] = '';

/* Permissions */
$txt['permissiongroup_TopicSolved_per_simple'] = 'Topic solved mod permissions';
$txt['permissiongroup_TopicSolved_per_classic'] = 'Topic Solved mod permissions';
$txt['permissionname_TopicSolved'] = 'Mark topics as solved';
$txt['permissionhelp_TopicSolved'] = 'This permission allows a user to mark a topic as solved.';
$txt['permissionname_TopicSolved_own'] = 'Own topic';
$txt['permissionname_TopicSolved_any'] = 'Any topic';
$txt['permissionname_simple_TopicSolved_own'] = 'Mark their own topic as solved';
$txt['permissionname_simple_TopicSolved_any'] = 'Mark anyone\'s topic as solved';