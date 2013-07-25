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

function TopicSolved_actions(&$actions)
{
	global $sourcedir;

	// A whole new action!
	$actions['topicSolved'] = array('TopicSolved.php', 'TopicSolved::call');
}

function TopicSolved_modifications(&$sub_actions)
{
	global $context;

	$sub_actions['topicSolved'] = 'TopicSolved_settings';
	$context[$context['admin_menu_name']]['tab_data']['tabs']['topicSolved'] = array();
}

function TopicSolved_admin_areas(&$areas)
{
	global $txt;

	if (!isset($txt['TopicSolved_main']))
		loadLanguage(TopicSolved::$name);

	$areas['config']['areas']['topicSolved'] = array(
		'label' => $txt['TopicSolved_main'],
		'file' => 'OAwardHooks.php',
		'function' => 'TopicSolved_index',
		'icon' => 'administration.gif',
		'subsections' => array(
			'general' => array($txt['TopicSolved_admin_title_general']),
		),
	);
}

function TopicSolved_index()
{
	global $txt, $scripturl, $context, $sourcedir;

	require_once($sourcedir . '/ManageSettings.php');
	loadLanguage(TopicSolved::$name);
	$context['page_title'] = $txt['TopicSolved_main'];

	$subActions = array(
		'general' => 'TopicSolved_settings',
	);

	// Time to overheat the server...
	$context['topicSolved']['object'] = new OAward();

	loadGeneralSettingParameters($subActions, 'general');

	if (is_callable($subActions[$_REQUEST['sa']]))
		call_user_func($subActions[$_REQUEST['sa']]);

	else
		redirectexit('action=admin;area=oaward');
}

function TopicSolved_settings(&$return_config = false)
{
	global $scripturl, $context, $sourcedir, $settings, $txt, $modSettings;

	loadtemplate('Admin');
	loadLanguage(TopicSolved::$name);

	// Extra check
	if (empty($context['topicSolved']['object']) || !is_object($context['topicSolved']['object']))
		$context['topicSolved']['object'] = new OAward();

	// Load stuff
	$context['sub_template'] = 'show_settings';
	$context['page_title'] = $txt['TopicSolved_admin_title_general'];
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['TopicSolved_admin_title_general'],
		'description' => $txt['TopicSolved_admin_desc'],
	);

	require_once($sourcedir . '/ManageServer.php');

	// A bunch of config settings here...
	$config_vars = array(
		array('desc', 'TopicSolved_admin_desc'),
		array('check', 'TopicSolved_admin_enable', 'subtext' => $txt['TopicSolved_admin_enable_sub']),
		array('text', 'TopicSolved_admin_1_color', 'size'=> 10, 'subtext' => $txt['TopicSolved_admin_1_color_sub']),
		array('text', 'TopicSolved_admin_2_color', 'size'=> 10, 'subtext' => $txt['TopicSolved_admin_2_color_sub']),
		array('text', 'TopicSolved_admin_3_color', 'size'=> 10, 'subtext' => $txt['TopicSolved_admin_3_color_sub']),
	);

	if ($return_config)
		return $config_vars;

	$context['post_url'] = $scripturl . '?action=admin;area=topicSolved;save;sa=general';
	$context['settings_title'] = $txt['TopicSolved_main'];

	if (empty($config_vars))
	{
		$context['settings_save_dont_show'] = true;
		$context['settings_message'] = '<div align="center">' . $txt['modification_no_misc_settings'] . '</div>';

		return prepareDBSettingContext($config_vars);
	}

	if (isset($_GET['save']))
	{

		checkSession();
		$save_vars = $config_vars;
		saveDBSettings($save_vars);
		redirectexit('action=admin;area=topicSolved;sa=general');
	}

	prepareDBSettingContext($config_vars);
}