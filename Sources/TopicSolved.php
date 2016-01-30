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
		$starter = $this->data('starter');
		$staff = $this->setting('staff') ? json_decode($this->setting('staff'), true) : array();

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
		$log_functions['topicsolvedlog'] = array('TopicSolvedAdmin.php', 'TopicSolvedAdmin::displayLog#', 'disabled' => !$this->enable('master'));
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
			'url' => $this->scriptUrl . '?action='. $this->name .';topic=' . $context['current_topic'] . ';is_solved='. $inverted .';starter='. $context['topicinfo']['id_member_started'],
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
