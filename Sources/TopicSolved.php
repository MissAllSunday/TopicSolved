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

// Use Ohara! manually :(
require_once ($sourcedir .'/ohara/src/Suki/Ohara.php');
require_once ($sourcedir .'/TopicSolvedTools.php');

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
		$is_solved = $this->data('is_solved');
		$topicS = $this->data('topic');

		// Meh...
		if (empty($is_solved) || empty($topicS))
			return redirectexit();

		// Change it!
		$this->changeStatus(array(
			'is_solved' => $is_solved,
			'topic' => $topicS
		));

		// Log the change.
		logAction($this->_statusFields[$is_solved], array(
			'topic' => $this->data('topic'),
			'board' => $board,
			'is_solved' => $is_solved,
		), $this->name);

		// Show a message of something...

		// Go back.
		return redirectexit('topic='. $topicS);
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
		$log_functions['topicsolvedlog'] = array('TopicSolved.php', 'TopicSolved::displayLog#', 'disabled' => !$this->enable('master'));
	}

	public function displayLog()
	{
		global $txt, $context, $scripturl, $sourcedir, $smcFunc, $user_info;

		loadLanguage($this->name);
		loadLanguage('Modlog');

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
							return $data['moderator_link'];
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
							return $data['is_solved'];
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
							'format' => '<input type="checkbox" name="notify_topics[]" value="%1$d" class="input_check">',
							'params' => array(
								'id' => false,
							),
						),
						'class' => 'centercol',
					),
				),
			),
			'form' => array(
				'href' => $scripturl . '?action=profile;area=notification;sa=topics',
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => array(
					'u' => $user_info['id'],
					'sa' => 'topicsolvedlog',
					$context['session_var'] => $context['session_id'],
				),
			),
		);

		// Create the notification list.
		createList($listOptions);
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'topicsolvedlog';
	}

	public function addDisplayTopic(&$topic_selects, &$topic_tables, &$topic_parameters)
	{
		// Mod's gotta be enable.
		if (!$this->enable('master'))
			return;

		$topic_selects[] = $this->_dbField;

		// Since we're already here...
		loadCSSFile($this->bootstrapCDN, array('external' => true));
		loadCSSFile('TopicSolved.css');
	}

	public function addMessageIndex(&$message_index_selects, &$message_index_tables, &$message_index_parameters)
	{
		// Mod's gotta be enable.
		if (!$this->innerCheck())
			return;

		$message_index_selects[] = $this->_dbField;

		// Since we're already here...
		loadCSSFile($this->bootstrapCDN, array('external' => true));
		loadCSSFile('TopicSolved.css');
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
		$(".'. $class .'").children(".board_icon").empty().addClass("'. $icon .'");';

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

		$confirmText = $this->parser($this->text('mark_as_solved_sure'), array(
			'status' => $this->text($this->_statusFields[$inverted])
		));

		$context['normal_buttons'][$this->name] = array(
			'text' => $this->name .'_mark_as_'. $this->_statusFields[$inverted],
			'lang' => true,
			'url' => $this->scriptUrl . '?action='. $this->name .';topic=' . $context['current_topic'] . ';is_solved='. $inverted,
			'class' => 'you_sure '. $this->_statusFields[$inverted],
			'custom' => 'data-confirm="'. $confirmText .'"'
		);
	}
}

/*
* So many fish there in the sea
* I wanted you, you wanted me
* That's just a phase, it's got to pass
* I was a train moving too fast
*/
