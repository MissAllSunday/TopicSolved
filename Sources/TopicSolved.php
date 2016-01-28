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

		// Go back.
		return redirectexit('topic='. $topicS);
	}

	public function addLog(&$log_functions)
	{
		global $context;

		// Menu stuff.
		$context[$context['admin_menu_name']]['tab_data']['tabs']['topicsolvedlog'] = array(
			'url' => $this->scriptUrl . '?action=admin;area=logs;sa=topicsolvedlog;desc',
			'label' => $this->text('log'),
			'description' => $this->text('log_desc'),
		);

		// Add our method.
		$log_functions['topicsolvedlog'] = array('TopicSolved.php', 'TopicSolved::displayLog#', 'disabled' => $this->enable('master'));
	}

	public function displayLog()
	{
		global $context, $smcFunc;

		loadtemplate($this->name);
		loadLanguage($this->name);

		$_subActions = array('delete');

		require_once($this->sourceDir . '/Subs-List.php');

		// Quick fix for lower php versions.
		$that = $this;

		// Get the log count.
		$totalLogs = $this->getTopicLogCount();

		// Do the topic notifications.
		$listOptions = array(
			'id' => 'topic_solved_log',
			'width' => '100%',
			'items_per_page' => 15,
			'no_items_label' => $this->text('log_none'),
			'no_items_align' => 'left',
			'base_href' => $this->scriptUrl . '?action=admin;area=logs;sa=topicsolvedlog',
			'default_sort_col' => 'desc',
			'get_items' => array(
				'function' => 'TopicSolvedTools::getLog#',
				'params' => array(),
			),
			'get_count' => array(
				'function' => function () use ($totalLogs){
					return $totalLogs;
				},
				'params' => array(),
			),
			'columns' => array(
				'subject' => array(
					'header' => array(
						'value' => $his->text('topic_title'),
						'class' => 'lefttext',
					),
					'data' => array(
						'function' => function ($data) use ($that)
						{
							return $data['link'];
						},
					),
					'sort' => array(
						'default' => 'ms.subject',
						'reverse' => 'ms.subject DESC',
					),
				),
				'started_by' => array(
					'header' => array(
						'value' => $txt['started_by'],
						'class' => 'lefttext',
					),
					'data' => array(
						'db' => 'poster_link',
					),
					'sort' => array(
						'default' => 'real_name_col',
						'reverse' => 'real_name_col DESC',
					),
				),
				'last_post' => array(
					'header' => array(
						'value' => $txt['last_post'],
						'class' => 'lefttext',
					),
					'data' => array(
						'sprintf' => array(
							'format' => '<span class="smalltext">%1$s<br>' . $txt['by'] . ' %2$s</span>',
							'params' => array(
								'updated' => false,
								'poster_updated_link' => false,
							),
						),
					),
					'sort' => array(
						'default' => 'ml.id_msg DESC',
						'reverse' => 'ml.id_msg',
					),
				),
				'alert' => array(
					'header' => array(
						'value' => $txt['notify_what_how'],
						'class' => 'lefttext',
					),
					'data' => array(
						'function' => function ($topic) use ($txt)
						{
							$pref = $topic['notify_pref'];
							$mode = !empty($topic['unwatched']) ? 0 : ($pref & 0x02 ? 3 : ($pref & 0x01 ? 2 : 1));
							return $txt['notify_topic_' . $mode];
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
					'u' => $memID,
					'sa' => $context['menu_item_selected'],
					$context['session_var'] => $context['session_id'],
				),
				'token' => $context['token_check'],
			),
			'additional_rows' => array(
				array(
					'position' => 'bottom_of_list',
					'value' => '<input type="submit" name="edit_notify_topics" value="' . $txt['notifications_update'] . '" class="button_submit" />
								<input type="submit" name="remove_notify_topics" value="' . $txt['notification_remove_pref'] . '" class="button_submit" />',
					'align' => 'right',
				),
			),
		);

		// Create the notification list.
		createList($listOptions);
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
