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

		$areas['maintenance']['areas']['logs']['subsections']['topicsolvedlog'] = array($this->text('modName'), 'TopicSolved::displayLog#', 'disabled' => !$this->enable('master'));
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
			$config_vars[] = array('select', $this->name .'_staff',
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
			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area='. $this->name .';sa='. $this->_sa);
		}

		prepareDBSettingContext($config_vars);
	}


	public function displayLog()
	{
		global $txt, $context, $scripturl, $sourcedir, $smcFunc, $user_info;

		loadLanguage($this->name);
		loadLanguage('Modlog');

		// Deleting?
		if ($this->validate('remove') || $this->validate('removeall'))
		{
			checkSession();
			validateToken('mod-ml');

			$deleteData = $this->data($this->name);

			if ((!empty($deleteData) && $this->validate('remove')))
				$this->deleteTopicLogs($deleteData);

			elseif($this->validate('removeall'))
				$this->deleteTopicLogs();
		}

		$context['page_title'] = $this->text('log_title');

		$_subActions = array('delete');
		$items_per_page = 15;
		$start = !empty($_REQUEST['start']) ? ((int) $_REQUEST['start']) : 0;
		$total = $this->getTopicLogCount();

		require_once($this->sourceDir . '/Subs-List.php');

		// Quick fix for lower php versions.
		$that = $this;

		// Do the topic notifications.
		$listOptions = array(
			'id' => 'topicsolvedlog',
			'width' => '100%',
			'title' => $this->text('log_title'),
			'items_per_page' => $items_per_page,
			'no_items_label' => $this->text('log_none'),
			'no_items_align' => 'left',
			'base_href' => $this->scriptUrl . '?action=admin;area=logs;sa=topicsolvedlog',
			'get_items' => array(
				'function' => $this->name .'::getTopicLogs#',
				'params' => array($start, $items_per_page),
			),
			'get_count' => array(
				'function' => function() use ($total)
				{
					return $total;
				},
			),
			'columns' => array(
				'subject' => array(
					'header' => array(
						'value' => $this->text('topic_title'),
						'class' => 'lefttext',
					),
					'data' => array(
						'function' => function ($data) use ($that)
						{
							return $data['link'];
						},
					),
				),
				'marked_by' => array(
					'header' => array(
						'value' => $this->text('log_marked_by'),
						'class' => 'lefttext',
					),
					'data' => array(
						'function' => function ($data) use ($that)
						{
							return $data['moderator_link'] .' - '. $data['position'];
						},
					),
				),
				'marked_as' => array(
					'header' => array(
						'value' => $this->text('log_marked_as'),
						'class' => 'lefttext',
					),
					'data' => array(
						'function' => function ($data) use ($that)
						{
							return $data['is_solved'] .' - '. $data['time'];
						},
					),
				),
				'delete' => array(
					'header' => array(
						'value' => '<input type="checkbox" class="input_check" onclick="invertAll(this, this.form);">',
						'style' => 'width: 4%;',
						'class' => 'centercol',
					),
					'data' => array(
						'sprintf' => array(
							'format' => '<input type="checkbox" name="'. $this->name .'[]" value="%1$d" class="input_check">',
							'params' => array(
								'id' => false,
							),
						),
						'class' => 'centercol',
					),
				),
			),
			'form' => array(
				'href' => $scripturl . '?action=admin;area=logs;sa=topicsolvedlog',
				'hidden_fields' => array(
					'u' => $user_info['id'],
					$context['session_var'] => $context['session_id'],
				),
				'token' => 'mod-ml',
			),
			'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '<input type="submit" name="remove" value="' . $txt['modlog_remove'] . '" data-confirm="' . $txt['modlog_remove_selected_confirm'] . '" class="button_submit you_sure">
					<input type="submit" name="removeall" value="' . $txt['modlog_removeall'] . '" data-confirm="' . $txt['modlog_remove_all_confirm'] . '" class="button_submit you_sure">',
				'class' => 'floatright',
			)),
		);

		// Create the notification list.
		createToken('mod-ml');
		createList($listOptions);
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'topicsolvedlog';
	}
}
