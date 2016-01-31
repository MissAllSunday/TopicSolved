<?php

/**
 * @package Topic Solved mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
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

if (!defined('SMF'))
	die('No direct access!');

global $txt, $scripturl;

$txt['TopicSolved_modName'] = 'Topic Solved';
$txt['TopicSolved_settingsGeneral'] = 'General';
$txt['TopicSolved_menuDesc'] = 'You can configure the Topic Solved mod here.';
$txt['TopicSolved_master'] = 'Enable the Topic Solved mod';
$txt['TopicSolved_master_sub'] = 'This is the master setting, must be enable for the mod to work properly';
$txt['TopicSolved_boards'] = 'The Board IDs where the mod will work';
$txt['TopicSolved_boards_sub'] = 'The marked boards will be the only places where the mod will act.';
$txt['TopicSolved_staff'] = 'Select the groups that will act as "staff".';
$txt['TopicSolved_staff_sub'] = 'Users within any group marked as "staff" will be used to reference when a topic can be marked as solved';
$txt['TopicSolved_lockTopic'] = 'Lock the topic when marked as solved';
$txt['TopicSolved_lockTopic_sub'] = 'Whenever a topic is marked as solved, the topic will also be locked.';
$txt['TopicSolved_lockTopic_op'] = 'The OP';
$txt['TopicSolved_lockTopic_staff'] = 'Staff';
$txt['TopicSolved_lockTopic_both'] = 'Staff or OP';
$txt['TopicSolved_lockTopicWhen'] = 'Lock the topic when marked by:';
$txt['TopicSolved_lockTopicWhen_sub'] = 'Chose which user can trigger locking a topic when marked as solved, options are:
<br>- '. $txt['TopicSolved_lockTopic_op'] .'. The topic will be locked only if the Original Poster marked the topic as solved
<br>- '. $txt['TopicSolved_lockTopic_staff'] .'. The topic will be locked only if someone marked as staff mark the topic as solved.
<br>- '. $txt['TopicSolved_lockTopic_both'] .'. The topic will be locked if the OP or someone marked as staff marks the topic as solved.';
$txt['TopicSolved_status'] = 'The status used by this mod';
$txt['TopicSolved_status_sub'] = 'By default the mod uses two types: solved and not solved.<br> You can change the font awesome icon if you want.'; 
$txt['TopicSolved_lockTopic'] = 'Lock the topic when marked as solved';
$txt['TopicSolved_lockTopic_sub'] = 'Whenever a topic is marked as solved, the topic will also be locked.';
$txt['TopicSolved_not_a_topic'] = 'The topic doesn\'t exists.';
$txt['TopicSolved_topic_solved_no_board'] = 'Sorry, topic solved feature is not enabled in this board.';
$txt['TopicSolved_staffRespond'] = 'Enable checking staff responses';
$txt['TopicSolved_staffRespond_sub'] = 'If enable, the topics where a staff member hasn\'t answered will be marked as not solved and if a staff member has replied but the OP hasn\'t, it will be marked as solved.';
$txt['TopicSolved_staffRespondStatus'] = 'Chose the status to mark those topics that haven\'t been replied by a staff member';
$txt['TopicSolved_staffRespondStatus_sub'] = 'When the scheduled task gets executed, any topic matching the criteria will be changed to this status.';
$txt['TopicSolved_daysNotResponded'] = 'How many days need to have passed before changing a topic status?';
$txt['TopicSolved_daysNotResponded_sub'] = 'As in, how many days a topic needs to stay without a reply from a staff member or the OP to change its status.';
$txt['TopicSolved_mark_as_solved'] = 'Mark as solved';
$txt['TopicSolved_mark_as_notsolved'] = 'Mark as not solved';
$txt['TopicSolved_mark_as_solved_sure'] = 'Are you sure you want to mark this topic as {status}?';
$txt['TopicSolved_solved'] = 'solved';
$txt['TopicSolved_notsolved'] = 'not solved';
$txt['TopicSolved_log_none'] = 'There are currently no topic solved entries';
$txt['TopicSolved_log_title'] = 'Topic Solved log';
$txt['TopicSolved_log_marked_by'] = 'Marked by';
$txt['TopicSolved_log_marked_as'] = 'Marked as';
$txt['TopicSolved_log_desc'] = 'The topic solved log tracks every topic been marked. To delete any errors from the database, mark the checkbox, and click the Remove button at the bottom of the page.';
$txt['TopicSolved_topic_title'] = 'Topic';
$txt['TopicSolved_disable_hook_title'] = '{modname} uses the following hooks';
$txt['TopicSolved_disable_hook_desc'] = 'Disabling a hook wil make SMF to not recognize it anymore, so be careful.<br> Admin and Settings hooks cannot be disable to prevent been locked out from the mod\'s settings page.';
$txt['TopicSolved_disable_hook'] = 'Disable {hook}';

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