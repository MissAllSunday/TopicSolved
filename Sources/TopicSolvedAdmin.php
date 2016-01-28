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

class TopicSolvedAdmin extends TopicSolvedTools
{
	public function __construct()
	{
		parent::__construct();
	}

	public function addPermissions(&$permissionGroups, &$permissionList)
	{
		loadLanguage($this->name);

		$permissionGroups['membergroup']['simple'] = array($this->name .'_per_simple');
		$permissionGroups['membergroup']['classic'] = array($this->name .'_per_classic');

		$permissionList['membergroup'][$this->name] = array(
			true,
			$this->name .'_per_simple',
			$this->name .'_per_classic');
	}

	public function addAdminArea(&$areas)
	{
		$areas['config']['areas'][$this->name] = array(
			'label' => $this->text('modName'),
			'file' => $this->name .'Admin.php',
			'function' => $this->name .'Admin::call#',
			'icon' => 'posts',
			'subsections' => array(
				'settings' => array($this->text('modName')),
			),
		);
	}

	public function call()
	{
		global $context;

		require_once($this->sourceDir . '/ManageSettings.php');

		$context['page_title'] = $this->text('modName');

		// Redundant much!?
		$subActions = array(
			'settings' => 'settings',
		);

		loadGeneralSettingParameters($subActions, 'settings');

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'tabs' => array(
				'settings' => array(),
			),
		);

		$this->_sa = isset($subActions[$this->data('sa')]) ? $subActions[$this->data('sa')] : 'settings';

		$this->{$this->_sa}();
	}

	function settings(&$return_config = false)
	{
		global $context, $txt;

		// Load stuff
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = $this->text('modName');
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this->text('menuDesc'),
		);

		require_once($this->sourceDir . '/ManageServer.php');

		// Set some default solved "status".
		if (!$this->enable('status'))
			updateSettings(array($this->name .'_status' => 'notsolved|fa fa-exclamation-triangle'. PHP_EOL .'solved|fa fa-check-square-o'));

		// A bunch of config settings here...
		$config_vars = array(
			array('desc', $this->name .'_menuDesc'),
			array('check', $this->name .'_master', 'subtext' => $this->text('master_sub')),
			array('boards', $this->name .'_boards', 'size' => 30, 'subtext' => $this->text('boards_sub')),
			array('check', $this->name .'_staffRespond', 'subtext' => $this->text('staffRespond_sub')),
		);

		$status = $this->getStatus();
		$statusAdmin = array();

		if (!empty($statusAdmin))
			$config_vars[] = array('select', $this->name .'_staffRespondStatus',
				$statusAdmin,
				'subtext' => $this->text('staffRespondStatus_sub'),
				'multiple' => true,
			);

		$config_vars[] = array('int', $this->name .'_daysNotResponded', 'size' => 3, 'subtext' => $this->text('daysNotResponded_sub'));
		$config_vars[] = array('check', $this->name .'_lockTopic', 'subtext' => $this->text('lockTopic_sub'));

		$config_vars[] = array('select', $this->name .'_lockTopicWhen',
			array(
				'op' => $txt['TopicSolved_lockTopic_op'],
				'staff' => $txt['TopicSolved_lockTopic_staff'],
				'both' => $txt['TopicSolved_lockTopic_both'],
			),
			'subtext' => $this->text('lockTopicWhen_sub'),
			'multiple' => false,
		);

		$config_vars[] = array('large_text', $this->name .'_status', '3" style="width:95%', 'subtext' => $this->text('status_sub'));

		// Are there any selectable groups?
		$groups = $this->getGroups();

		if (!empty($groups))
			$config_vars[] = array('select', $this->name .'_selectGroups',
				$groups,
				'subtext' => $this->text('selectGroups_sub'),
				'multiple' => true,
			);

		// Disable this mod's hooks.
		$this->disableHooks($config_vars);

		if ($return_config)
			return $config_vars;

		$context['post_url'] = $this->scriptUrl . '?action=admin;area='. $this->name .';save;sa='. $this->_sa;
		$context['settings_title'] = $this->text('modName');

		if (empty($config_vars))
		{
			$context['settings_save_dont_show'] = true;
			$context['settings_message'] = '<div align="center">' . $txt['modification_no_misc_settings'] . '</div>';

			return prepareDBSettingContext($config_vars);
		}

		if ($this->validate('save'))
		{
			// Have to directly mess with the super duper global!
			$_POST[$this->name .'_boards'] = $this->commaSeparated($this->data($this->name . '_boards'), 'numeric');

			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area='. $this->name .';sa='. $this->_sa);
		}

		prepareDBSettingContext($config_vars);
	}

	protected function getGroups()
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT id_group, group_name
			FROM {db_prefix}membergroups
			WHERE id_group > {int:admin}',
			array(
				'admin' => 1,
			)
		);
		$return = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$return[$row['id_group']] = $row['group_name'];

		$smcFunc['db_free_result']($request);

		return $return;
	}
}
