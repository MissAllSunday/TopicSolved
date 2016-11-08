<?php

/**
 * @package Topic Solved mod
 * @version 1.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright 2016 Suki
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (!defined('SMF'))
	die('No direct access!');

// Ohara autoload!
require_once $sourcedir .'/ohara/src/Suki/autoload.php';
require_once ($sourcedir .'/TopicSolvedTools.php');

use Suki\Ohara;

class TopicSolved extends TopicSolvedTools
{
	public $name = __CLASS__;
	public $bootstrapCDN = '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css';

	public function __construct()
	{
		parent::__construct();
	}

	public function call()
	{
		global $board, $user_info;

		// Get the solved ID.
		$is_solved = $this['data']->get('is_solved');
		$topicS = $this['data']->get('topic');
		$starter = $this['data']->get('starter');
		$staff = $this->enable('staff') ? json_decode($this->setting('staff'), true) : array();

		// Meh...
		if (empty($is_solved) || empty($topicS))
			return $this['tools']->redirect();

		// Change it!
		$this->changeStatus(array(
			'is_solved' => $is_solved,
			'topic' => $topicS
		));

		// Log the change.
		logAction($this->_statusFields[$is_solved], array(
			'topic' => $topicS,
			'board' => $board,
			'is_solved' => $is_solved,
		), $this->name);

		// Lock the topic if needed.
		if ($this->enable('lockTopic'))
		{
			// Lock or unlock?
			$lock = $is_solved == 2 ? 1 : 0;

			// The OP?
			if (($this->setting('lockTopicWhen')  == 'op' || $this->setting('lockTopicWhen')  == 'both') && ($starter && $starter == $user_info['id']))
				$this->lockTopic($topicS, $lock);

			// Perhaps a staff member?
			elseif (($this->setting('lockTopicWhen')  == 'staff' || $this->setting('lockTopicWhen')  == 'both') && !empty($staff) && array_intersect($user_info['groups'], $staff))
				$this->lockTopic($topicS, $lock);
		}

		// Go back.
		return $this['tools']->redirect('topic='. $topicS);
	}

	public function addLogType(&$log_types)
	{
		$log_types[$this->name] = $this->logType;
	}

	public function addLog(&$log_functions)
	{
		global $context;

		// Menu stuff.
		$context[$context['admin_menu_name']]['tab_data']['tabs']['topicsolvedlog'] = array(
			'url' => $this->scriptUrl . '?action=admin;area=logs;sa=topicsolvedlog',
			'label' => $this->text('log_title'),
			'description' => $this->text('log_desc'),
		);

		// Add our method.
		$log_functions['topicsolvedlog'] = array($this->name .'.php', $this->name .'::displayLog#', 'disabled' => !$this->enable('master'));
	}

	public function addDisplayTopic(&$topic_selects, &$topic_tables, &$topic_parameters)
	{
		// Mod's gotta be enable.
		if (!$this->enable('master'))
			return;

		$topic_selects[] = $this->_dbField;

		// Since we're already here...
		loadCSSFile($this->bootstrapCDN, array('external' => true));
		loadCSSFile($this->name .'.css');
	}

	public function addMessageIndex(&$message_index_selects, &$message_index_tables, &$message_index_parameters)
	{
		// Mod's gotta be enable.
		if (!$this->innerCheck())
			return;

		$message_index_selects[] = $this->_dbField;

		// Since we're already here...
		loadCSSFile($this->bootstrapCDN, array('external' => true));
		loadCSSFile($this->name .'.css');
		loadLanguage($this->name);
	}

	public function addMessageButtons()
	{
		global $context;

		// No topics or the mod isn't enable?
		if (empty($context['topics']) || !$this->innerCheck())
			return false;

		// Append the corresponding solved class.
		foreach ($context['topics'] as $id => $topic)
		{
			// Only add any css class if the topic has been marked.
			if (empty($topic['is_solved']))
				continue;

			$context['topics'][$id]['css_class'] = $context['topics'][$id]['css_class'] . ' '. $this->_statusFields[$topic['is_solved']];
		}

		// Create the needed JS stuff!
		$injectJS  = '
	$(function()
	{';

		// Because reasons!
		foreach ($this->getStatus() as $class => $icon)
			$injectJS  .= '
		$(".'. $this['data']->sanitize($class) .'").children(".board_icon").empty().addClass("'. $this['data']->sanitize($icon) .'");';

		// Close the JS
		$injectJS  .= '
	});';

		// Add our lovely JS!
		addInlineJavascript($injectJS, true);
	}

	public function addDisplayButtons()
	{
		global $context, $user_info;

		// Mod's gotta be enable and you need permissions.
		if (!$this->innerCheck() || !$this->checkPermissions($context['topicinfo']['id_member_started']))
			return;

		loadLanguage($this->name);

		$context['topicinfo']['is_solved'] = (int) $context['topicinfo']['is_solved'];

		// Invert the roles!
		$inverted = ($context['topicinfo']['is_solved'] != 2) ? 2 : 1;

		$confirmText = $this['tools']->parser($this->text('mark_as_solved_sure'), array(
			'status' => $this->text($this->_statusFields[$inverted])
		));

		$context['normal_buttons'][$this->name] = array(
			'text' => $this->name .'_mark_as_'. $this->_statusFields[$inverted],
			'lang' => true,
			'url' => $this->scriptUrl . '?action='. $this->name .';topic=' . $context['current_topic'] . ';is_solved='. $inverted .';starter='. $context['topicinfo']['id_member_started'],
			'class' => 'you_sure '. $this->_statusFields[$inverted],
			'custom' => 'data-confirm="'. $confirmText .'"'
		);
	}

	public function scheduledTask()
	{
		global $smcFunc;

		// Mod's gotta be enable and the "check staff responses" setting needs to be on! also, at least 1 day has to pass.
		if (!$this->enable('master') || !$this->enable('staffRespond') || !$this->enable('daysNotResponded'))
			return true;

		$days = $this->enable('daysNotResponded');
		$staff = $this->enable('staff') ? json_decode($this->setting('staff'), true) : array();
		$tBoards = explode(',', $this->setting('boards'));

		// Get the start and end timestamps.
		$dayIni = new DateTime('-'. $days .' days 00:00');
		$dayEnd = new DateTime('-'. $days .' days 23:59');
		$to = $dayEnd->format('U');
		$from = $dayIni->format('U');
		$data = array();
		$solved = array();
		$not = array();

		// Queries, queries everywhere!
		$request = $smcFunc['db_query']('', '
			SELECT t.id_topic, t.id_first_msg, t.id_member_started AS member_start, t.id_last_msg, t.is_solved, ml.id_member AS member_last, ml.poster_time, m.id_post_group, m.additional_groups, m.id_group, l.id_member AS member_solved, l.extra
			FROM {db_prefix}topics as t
				LEFT JOIN {db_prefix}messages as ml ON (ml.id_msg = t.id_last_msg)
				LEFT JOIN {db_prefix}members as m ON (m.id_member = ml.id_member)
				LEFT JOIN {db_prefix}log_actions as l ON (l.id_topic = t.id_topic AND l.id_log = {int:logType})
			WHERE t.id_board IN ({array_int:boards})
				AND (ml.poster_time BETWEEN {int:from} AND {int:to})
			', array(
				'from' => $from,
				'to' => $to,
				'boards' => $tBoards,
				'logType' => $this->logType,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$data[$row['id_topic']] = $row;

		$smcFunc['db_free_result']($request);

		// Nada?
		if (empty($data))
			return true;

		// OK... time to do some checks.
		foreach ($data as $k => $v)
		{
			$data['extra'] = !empty($data['extra']) ? json_decode($data['extra'], true) : array();

			// Was the OP the last poster and the one who marked the topic?
			if ($v['member_start'] == $v['member_last'] && $v['member_last'] != $v['member_solved'])
				$not[] = $k;

			// No? then check if its a staff user.
			else
			{
				$groups = !empty($v['additional_groups']) ? explode(',', $v['additional_groups']) : array();

				$groups[] = $v['id_group'];

				if (!empty($v['id_post_group']))
					$groups[] = $v['id_post_group'];

				if (array_intersect($groups, $staff) && $v['member_last'] != $v['member_solved'])
					$solved[] = $k;
			}
		}

		// Mark as not solved.
		if (!empty($not))
		{
			$this->changeStatus(array(
				'is_solved' => 1,
				'topic' => $not,
			));

			// Also unlock the topic if needed.
			if ($this->enable('lockTopic') && $this->setting('lockTopicWhen')  == 'op' || $this->setting('lockTopicWhen')  == 'both')
				$this->lockTopic($not, 0);
		}

		// Mark as solved.
		if (!empty($solved))
		{
			$this->changeStatus(array(
				'is_solved' => 2,
				'topic' => $solved,
			));

			// Also lock the topic if needed.
			if ($this->enable('lockTopic') && $this->setting('lockTopicWhen')  == 'staff' || $this->setting('lockTopicWhen') == 'both')
				$this->lockTopic($solved, 1);
		}

		// Done!
		return true;
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
			'file' => $this->name .'.php',
			'function' => array($this, 'adminCall'),
			'icon' => 'settings',
			'subsections' => array(
				'settings' => array($this->text('modName')),
			),
		);
		$areas['maintenance']['areas']['logs']['subsections']['topicsolvedlog'] = array($this->text('modName'), array($this, 'displayLog'), 'disabled' => !$this->enable('master'));
	}

	public function adminCall()
	{
		global $context;

		require_once($this->sourceDir . '/ManageSettings.php');

		$context['page_title'] = $this->text('modName');
		$sa = $this['data']->get('sa', 'settings');

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

		$this->_sa = isset($subActions[$sa]) ? $subActions[$sa] : 'settings';
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

		// Permissions.
		$config_vars[] = array('permissions', $this->name .'_own', 0, $txt['permissionname_simple_TopicSolved_own']);
		$config_vars[] = array('permissions', $this->name .'_any', 0, $txt['permissionname_simple_TopicSolved_any']);

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

		if ($this['data']->validate('save'))
		{
			checkSession();
			saveDBSettings($config_vars);
			$this['tools']->redirect('action=admin;area='. $this->name .';sa='. $this->_sa);
		}

		prepareDBSettingContext($config_vars);
	}


	public function displayLog()
	{
		global $txt, $context, $scripturl, $sourcedir, $smcFunc, $user_info;

		loadLanguage($this->name);
		loadLanguage('Modlog');

		// Deleting?
		if ($this['data']->validate('remove') || $this['data']->validate('removeall'))
		{
			checkSession();
			validateToken('mod-ml');

			$deleteData = $this['data']->get($this->name);

			if ((!empty($deleteData) && $this['data']->validate('remove')))
				$this->deleteTopicLogs($deleteData);

			elseif($this['data']->validate('removeall'))
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
						'function' => function ($data)
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
						'function' => function ($data)
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
						'function' => function ($data)
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

/*
* So many fish there in the sea
* I wanted you, you wanted me
* That's just a phase, it's got to pass
* I was a train moving too fast
*/
