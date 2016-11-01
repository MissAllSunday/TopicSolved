<?php

/**
 * @package Topic Solved mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2016 Suki
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

else if(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php and SSI.php files.');

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin priveleges required.');

// Prepare and insert this mod's config array.
$_config = array(
	'_availableHooks' => array(
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
	),
);

// All good.
updateSettings(array('_configTopicSolved' => json_encode($_config)));

if (SMF == 'SSI')
	echo 'Database changes are complete!';