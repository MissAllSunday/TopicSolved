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

class TopicSolvedAdmin extends TopicSolved
{
	public function __construct()
	{

	}

	public function call()
	{
		global $context;

		require_once($this->sourceDir . '/ManageSettings.php');

		$context['page_title'] = $this->text('menuTitle');

		$subActions = array(
			'settings' => 'settings',
		);

		loadGeneralSettingParameters($subActions, 'settings');

		$this->_data = $this->data('request');

		if ($this->_data['sa'] && in_array($this->_data['sa'], $subActions))
			$this->{$subActions[$this->_data['sa']]}();

		else
			redirectexit('action=admin;area=oaward');
	}

	function settings(&$return_config = false)
	{
		global $context;

		// Load stuff
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = $this->text('menuTitle');
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this->text('menuDesc'),
		);

		require_once($sourcedir . '/ManageServer.php');

		// A bunch of config settings here...
		$config_vars = array(
			array('desc', $this->name .'_menuDesc'),
			array('check', $this->name .'_enable', 'subtext' => $txt['TopicSolved_enable_sub']),
			array('text', $this->name .'_boards', 'subtext' => $txt['TopicSolved_boards_sub']),
		);

		if ($return_config)
			return $config_vars;

		$context['post_url'] = $this->scriptUrl . '?action=admin;area='. $this->name .';save;sa=general';
		$context['settings_title'] = $this->text('menuTitle');

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
}